<?php

namespace App\Http\Controllers;

use App\Models\Scourses;
use Illuminate\Http\Request;

class ScoursesController extends Controller
{
    public function create(Request $request)
    {
        $this->validate($request, [
            'studentId' => 'required|string',
            'contents' => 'required|array',
        ]);

        $studentId = $request->input('studentId');
        $contents = $request->input('contents');

        $courseIds = [];
        $incoming = [];

        foreach ($contents as $c) {
            $courseId = $c['courseId'] ?? '';
            $title = $c['title'] ?? '';
            if (trim($courseId) !== '') {
                $courseIds[] = $courseId;
                $incoming[] = [
                    'courseId' => $courseId,
                    'title' => $title
                ];
            }
        }

        if (empty($courseIds)) {
            return response("contents must include at least one courseId", 400);
        }

        // Fetch existing doc or initialize new one
        $scourse = Scourses::where('studentId', $studentId)->first();
        $created = false;

        if (!$scourse) {
            $scourse = new Scourses();
            $scourse->studentId = $studentId;
            $scourse->contents = [];
            $created = true;
        }

        // Check for duplicates
        if ($scourse && !empty($scourse->contents)) {
            foreach ($scourse->contents as $item) {
                if (isset($item['courseId']) && in_array($item['courseId'], $courseIds)) {
                    return response("One or more courses already exist for this student", 409);
                }
            }
        }

        // Add new contents (deduplicating by courseId case-insensitive)
        $existingContents = $scourse->contents ?? [];
        $existingCourseIdsLower = array_map(function($item) {
            return isset($item['courseId']) ? strtolower($item['courseId']) : '';
        }, $existingContents);

        foreach ($incoming as $item) {
            $key = strtolower($item['courseId']);
            if (!in_array($key, $existingCourseIdsLower)) {
                $existingContents[] = $item;
                $existingCourseIdsLower[] = $key;
            }
        }

        $scourse->contents = $existingContents;
        $scourse->save();

        if ($created) {
            return response()->json($scourse, 201, [
                'Location' => '/api/scourses/enroll' . $scourse->id
            ]);
        }

        return response()->json($scourse);
    }

    public function all()
    {
        return response()->json(Scourses::all());
    }

    public function byStudentId($studentId)
    {
        $scourse = Scourses::where('studentId', $studentId)->first();
        if (!$scourse) {
            return response()->json(null, 404);
        }
        return response()->json($scourse);
    }
}
