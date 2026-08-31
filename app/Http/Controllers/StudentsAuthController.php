<?php

namespace App\Http\Controllers;

use App\Models\Students;
use App\Services\JwtService;
use Illuminate\Http\Request;

class StudentsAuthController extends Controller
{
    protected $jwt;

    public function __construct(JwtService $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        $student = Students::where('email', $email)->first();
        if (!$student) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        $hashedInput = hash('sha256', $password);
        if (strcasecmp($hashedInput, $student->password) !== 0) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        $token = $this->jwt->generate($student->id, $student->email, $student->full_name);

        $userView = [
            'id' => $student->id,
            'email' => $student->email,
            'full_name' => $student->full_name,
            'phone' => $student->phone,
            'createdAt' => $student->createdAt ? $student->createdAt->toIso8601String() : null,
        ];

        return response()->json([
            'user' => $userView,
            'accessToken' => $token
        ]);
    }

    public function me(Request $request)
    {
        // First check session if active
        if ($request->hasSession() && $request->session()->has('student')) {
            return response()->json($request->session()->get('student'));
        }

        // Check JWT Bearer token
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            $payload = $this->jwt->parse($token);
            if ($payload && isset($payload['sub'])) {
                $student = Students::find($payload['sub']);
                if ($student) {
                    return response()->json([
                        'id' => $student->id,
                        'email' => $student->email,
                        'full_name' => $student->full_name,
                        'phone' => $student->phone,
                        'createdAt' => $student->createdAt ? $student->createdAt->toIso8601String() : null,
                    ]);
                }
            }
        }

        return response()->json(['error' => 'Not logged in'], 401);
    }
}
