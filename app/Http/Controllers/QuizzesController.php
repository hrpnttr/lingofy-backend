<?php

namespace App\Http\Controllers;

use App\Models\Quizzes;
use Illuminate\Http\Request;

class QuizzesController extends Controller
{
    public function list(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $page = (int) $request->input('page', 0);
        $size = (int) $request->input('size', 10);
        $q = $request->input('q', '');

        $query = Quizzes::query();
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);

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

        $quizQuery = Quizzes::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $quizQuery->where('tenant_id', $tenantId);
        $quiz = $quizQuery->first();
        if (!$quiz) {
            return response()->json(null, 404);
        }

        return response()->json($quiz);
    }

    public function get(Request $request, $id)
    {
        $query = Quizzes::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $quiz = $query->first();
        if (!$quiz) {
            return response()->json(null, 404);
        }
        return response()->json($quiz);
    }
}
