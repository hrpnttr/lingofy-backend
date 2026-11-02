<?php

namespace App\Http\Controllers;

use App\Models\Students;
use Illuminate\Http\Request;

class StudentsController extends Controller
{
    public function create(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $this->validate($request, [
            'full_name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->input('email');
        $existingQuery = Students::where('email', $email);
        if ($tenantId !== null) $existingQuery->where('tenant_id', $tenantId);
        if ($existingQuery->exists()) {
            return response("Email already exists", 409);
        }

        $studentData = [
            'full_name' => $request->input('full_name'),
            'phone' => $request->input('phone'),
            'email' => $email,
            'password' => password_hash($request->input('password'), PASSWORD_DEFAULT),
        ];
        if ($tenantId !== null) $studentData['tenant_id'] = $tenantId;
        $student = Students::create($studentData);

        return response()->json($student, 201, [
            'Location' => '/api/students/' . $student->id
        ]);
    }

    public function all(Request $request)
    {
        $query = Students::query();
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        return response()->json($query->get());
    }

    public function byId(Request $request, $id)
    {
        $query = Students::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $student = $query->first();
        if (!$student) {
            return response()->json(null, 404);
        }
        return response()->json($student);
    }

    public function byEmail(Request $request)
    {
        $email = $request->input('email');
        if (!$email) {
            return response()->json(null, 404);
        }

        $query = Students::where('email', $email);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $student = $query->first();
        if (!$student) {
            return response()->json(null, 404);
        }
        return response()->json($student);
    }
}
