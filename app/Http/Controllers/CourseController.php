<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function create(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $this->validate($request, [
            'title' => 'required|string',
            'description' => 'required|string',
            'level' => 'required|string',
            'language' => 'required|string',
        ]);

        $courseData = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'level' => $request->input('level'),
            'language' => $request->input('language'),
            'price' => $request->input('price'),
            'durationHours' => $request->input('durationHours'),
            'tags' => $request->input('tags'),
            'published' => false,
        ];
        if ($tenantId !== null) {
            $courseData['tenant_id'] = $tenantId;
        }
        $course = Courses::create($courseData);

        return response()->json($course, 201, [
            'Location' => '/api/course/' . $course->id
        ]);
    }

    public function list(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $page = (int) $request->input('page', 0);
        $size = (int) $request->input('size', 10);
        $q = $request->input('q', '');
        $level = $request->input('level', '');
        $language = $request->input('language', '');

        $query = Courses::query();
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        if (trim($q) !== '') {
            $query->where('title', 'like', '%' . $q . '%');
        }
        if (trim($level) !== '') {
            $query->where('level', $level);
        }
        if (trim($language) !== '') {
            $query->where('language', $language);
        }

        $query->orderBy('createdAt', 'desc');

        $total = $query->count();
        $items = $query->skip($page * $size)->take($size)->get();

        return response()->json($this->formatPage($items, $total, $page, $size));
    }

    public function get(Request $request, $id)
    {
        $query = Courses::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $course = $query->first();
        if (!$course) {
            return response()->json(null, 404);
        }
        return response()->json($course);
    }

    public function update(Request $request, $id)
    {
        $query = Courses::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $course = $query->first();
        if (!$course) {
            return response()->json(null, 404);
        }

        $fields = [
            'title', 'description', 'level', 'language',
            'price', 'durationHours', 'tags', 'published'
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $val = $request->input($field);
                if (in_array($field, ['title', 'description', 'level', 'language'])) {
                    if ($val !== null && trim($val) !== '') {
                        $course->{$field} = $val;
                    }
                } else {
                    $course->{$field} = $val;
                }
            }
        }

        $course->save();

        return response()->json($course);
    }

    public function delete(Request $request, $id)
    {
        $query = Courses::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $course = $query->first();
        if (!$course) {
            return response()->json(null, 404);
        }
        $course->delete();
        return response()->json(null, 204);
    }
}
