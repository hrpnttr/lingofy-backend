<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Courses;
use Illuminate\Http\Request;

class ClassesController extends Controller
{
    public function list(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $page = (int) $request->input('page', 0);
        $size = (int) $request->input('size', 10);
        $q = $request->input('q', '');

        $query = Classes::query();
        if ($tenantId !== null) {
            $query->whereIn('course_id', Courses::select('id')->where('tenant_id', $tenantId));
        }

        if (trim($q) !== '') {
            $query->where('class_name', 'like', '%' . $q . '%');
        }

        $query->orderBy('id', 'desc');

        $total = $query->count();
        $items = $query->skip($page * $size)->take($size)->get();

        return response()->json($this->formatPage($items, $total, $page, $size));
    }

    public function get(Request $request, $id)
    {
        $query = Classes::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->whereIn('course_id', Courses::select('id')->where('tenant_id', $tenantId));
        $class = $query->first();
        if (!$class) {
            return response()->json(null, 404);
        }
        return response()->json($class);
    }
}
