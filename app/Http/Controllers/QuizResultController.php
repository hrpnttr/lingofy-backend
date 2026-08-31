<?php

namespace App\Http\Controllers;

use App\Models\QuizzResult;
use Illuminate\Http\Request;

class QuizResultController extends Controller
{
    public function list(Request $request)
    {
        $page = (int) $request->input('page', 0);
        $size = (int) $request->input('size', 10);

        $query = QuizzResult::query();
        $query->orderBy('id', 'desc');

        $total = $query->count();
        $items = $query->skip($page * $size)->take($size)->get();

        return response()->json($this->formatPage($items, $total, $page, $size));
    }

    public function get($id)
    {
        $result = QuizzResult::find($id);
        if (!$result) {
            return response()->json(null, 404);
        }
        return response()->json($result);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'studentId' => 'required|string',
            'quiz_id' => 'required|string',
            'answers' => 'required|array',
            'score' => 'required|integer',
        ]);

        $answers = $request->input('answers');
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
}
