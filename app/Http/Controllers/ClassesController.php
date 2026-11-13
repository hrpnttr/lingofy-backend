<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Courses;
use App\Models\Quizzes;
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
        $items = $this->withoutInactiveQuizMaterials($query->skip($page * $size)->take($size)->get());

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
        return response()->json($this->withoutInactiveQuizMaterials(collect([$class]))->first());
    }

    /**
     * Drop the quiz entry from a class's materials when that class has no
     * active quiz, so students don't see a quiz an admin has deactivated.
     */
    private function withoutInactiveQuizMaterials($classes)
    {
        $classIdsWithActiveQuiz = Quizzes::active()
            ->whereIn('class_id', $classes->pluck('id'))
            ->pluck('class_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $classes->each(function (Classes $class) use ($classIdsWithActiveQuiz) {
            if (in_array((int) $class->id, $classIdsWithActiveQuiz, true) || !is_array($class->materials)) {
                return;
            }
            $class->materials = array_values(array_filter(
                $class->materials,
                fn ($material) => !is_array($material) || ($material['type'] ?? null) !== 'quiz'
            ));
        });
    }
}
