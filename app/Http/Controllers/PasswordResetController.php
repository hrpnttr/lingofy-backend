<?php

namespace App\Http\Controllers;

use App\Models\Students;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $student = Students::where('email', $email)->first();
        if (!$student) {
            return response()->json(null, 404);
        }

        return response()->json($student);
    }

    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->input('email');
        $student = Students::where('email', $email)->first();
        if (!$student) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        $student->password = hash('sha256', $request->input('password'));
        $student->save();

        return response()->json($student);
    }
}
