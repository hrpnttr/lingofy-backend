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
        $query = Students::where('email', $email);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $student = $query->first();
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
        $query = Students::where('email', $email);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $student = $query->first();
        if (!$student) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        $student->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
        $student->save();

        return response()->json($student);
    }
}
