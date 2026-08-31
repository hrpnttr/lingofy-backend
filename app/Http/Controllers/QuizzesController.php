<?php

namespace App\Http\Controllers;

use App\Models\Quizzes;
use Illuminate\Http\Request;

class QuizzesController extends Controller
{
    public function list(Request $request)
    {
        $page = (int) $request->input('page', 0);
        $size = (int) $request->input('size', 10);
        $q = $request->input('q', '');

        $query = Quizzes::query();

        if (trim($q) !== '') {
            $query->where('title', 'like', '%' . $q . '%');
        }

        $query->orderBy('id', 'desc');

        $total = $query->count();
        $items = $query->skip($page * $size)->take($size)->get();

        return response()->json($this->formatPage($items, $total, $page, $size));
    }

    public function byId(Request $request)
    {
        $id = $request->query('id');
        if (empty($id)) {
            return response("Query param 'id' is required", 400);
        }

        if (!is_numeric($id)) {
            return response("'id' must be a numeric value", 400);
        }

        $quiz = Quizzes::find($id);
        if (!$quiz) {
            return response()->json(null, 404);
        }

        return response()->json($quiz);
    }

    public function get($id)
    {
        $quiz = Quizzes::find($id);
        if (!$quiz) {
            return response()->json(null, 404);
        }
        return response()->json($quiz);
    }
}
