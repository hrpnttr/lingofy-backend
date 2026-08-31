<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use Illuminate\Http\Request;

class ClassesController extends Controller
{
    public function list(Request $request)
    {
        $page = (int) $request->input('page', 0);
        $size = (int) $request->input('size', 10);
        $q = $request->input('q', '');

        $query = Classes::query();

        if (trim($q) !== '') {
            $query->where('class_name', 'like', '%' . $q . '%');
        }

        $query->orderBy('id', 'desc');

        $total = $query->count();
        $items = $query->skip($page * $size)->take($size)->get();

        return response()->json($this->formatPage($items, $total, $page, $size));
    }

    public function get($id)
    {
        $class = Classes::find($id);
        if (!$class) {
            return response()->json(null, 404);
        }
        return response()->json($class);
    }
}
