<?php

namespace App\Http\Controllers;

use App\Models\Students;
use Illuminate\Http\Request;

class StudentsController extends Controller
{
    public function create(Request $request)
    {
        $this->validate($request, [
            'full_name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->input('email');
        if (Students::where('email', $email)->exists()) {
            return response("Email already exists", 409);
        }

        $student = Students::create([
            'full_name' => $request->input('full_name'),
            'phone' => $request->input('phone'),
            'email' => $email,
            'password' => hash('sha256', $request->input('password')),
        ]);

        return response()->json($student, 201, [
            'Location' => '/api/students/' . $student->id
        ]);
    }

    public function all()
    {
        return response()->json(Students::all());
    }

    public function byId($id)
    {
        $student = Students::find($id);
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

        $student = Students::where('email', $email)->first();
        if (!$student) {
            return response()->json(null, 404);
        }
        return response()->json($student);
    }
}
