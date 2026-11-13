<?php

namespace App\Http\Controllers;

use App\Models\QuizzResult;
use App\Models\Students;
use App\Models\Quizzes;
use App\Support\QuizScorer;
use Illuminate\Http\Request;

class QuizResultController extends Controller
{
    public function list(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $page = (int) $request->input('page', 0);
        $size = (int) $request->input('size', 10);

        $query = QuizzResult::query();
        if ($tenantId !== null) $query->whereIn('studentId', Students::select('id')->where('tenant_id', $tenantId));
        $query->orderBy('id', 'desc');

        $total = $query->count();
        $items = $query->skip($page * $size)->take($size)->get();

        return response()->json($this->formatPage($items, $total, $page, $size));
    }

    public function get(Request $request, $id)
    {
        $query = QuizzResult::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->whereIn('studentId', Students::select('id')->where('tenant_id', $tenantId));
        $result = $query->first();
        if (!$result) {
            return response()->json(null, 404);
        }
        return response()->json($result);
    }

    public function create(Request $request)
    {
        if ($request->has('responses')) {
            return $this->createScored($request);
        }

        $tenantId = $this->tenantId($request);
        $this->validate($request, [
            'studentId' => 'required|string',
            'quiz_id' => 'required|string',
            'answers' => 'required|array',
            'score' => 'required|integer',
        ]);

        $answers = $request->input('answers');
        if ($tenantId !== null) {
            $studentId = $request->input('studentId');
            $quizId = $request->input('quiz_id');
            if (!Students::where('id', $studentId)->where('tenant_id', $tenantId)->exists()
                || !Quizzes::where('id', $quizId)->where('tenant_id', $tenantId)->exists()) {
                return response()->json(['message' => 'Student or quiz not found in this organization.'], 404);
            }
        }
        if (!Quizzes::active()->where('id', $request->input('quiz_id'))->exists()) {
            return response()->json(['message' => 'This quiz is not available.'], 404);
        }
        $mappedAnswers = [];
        foreach ($answers as $ans) {
            $mappedAnswers[] = [
                'text' => $ans['text'] ?? '',
                'is_correct' => (bool) ($ans['is_correct'] ?? false),
            ];
        }

        $result = QuizzResult::create([
            'studentId' => $request->input('studentId'),
            'quiz_id' => $request->input('quiz_id'),
            'answers' => $mappedAnswers,
            'score' => $request->input('score'),
        ]);

        return response()->json($result, 201, [
            'Location' => '/api/quizzes/' . $result->id
        ]);
    }

    /**
     * Server-scored submission: the client sends raw responses only; correctness
     * and points are computed here from the stored quiz.
     */
    private function createScored(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $this->validate($request, [
            'studentId' => 'required|string',
            'quiz_id' => 'required|string',
            'responses' => 'present|array',
        ]);

        $studentId = $request->input('studentId');
        $quizId = $request->input('quiz_id');

        $quizQuery = Quizzes::active()->where('id', $quizId);
        if ($tenantId !== null) {
            $quizQuery->where('tenant_id', $tenantId);
            if (!Students::where('id', $studentId)->where('tenant_id', $tenantId)->exists()) {
                return response()->json(['message' => 'Student or quiz not found in this organization.'], 404);
            }
        }
        $quiz = $quizQuery->first();
        if (!$quiz) {
            return response()->json(['message' => 'Student or quiz not found in this organization.'], 404);
        }

        $questions = is_array($quiz->contens) ? array_values($quiz->contens) : [];
        if (count($questions) === 0) {
            return response()->json(['message' => 'This quiz has no questions.'], 422);
        }

        [$errors, $values] = QuizScorer::validate($questions, $request->input('responses'));
        if ($errors) {
            return response()->json([
                'message' => reset($errors),
                'errors' => array_map(fn ($m) => [$m], $errors),
            ], 422);
        }

        $scored = QuizScorer::score($questions, $values);

        $result = QuizzResult::create([
            'studentId' => $studentId,
            'quiz_id' => $quizId,
            'answers' => $scored['answers'],
            'score' => $scored['score'],
        ]);

        return response()->json(array_merge($result->toArray(), [
            'points_earned' => $scored['points_earned'],
            'points_max' => $scored['points_max'],
            'pending_review' => $scored['pending_review'],
        ]), 201, [
            'Location' => '/api/quizzes/' . $result->id
        ]);
    }
}
