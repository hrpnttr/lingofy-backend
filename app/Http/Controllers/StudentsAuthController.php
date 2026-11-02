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

        $tenantId = $this->tenantId($request);
        $studentQuery = Students::where('email', $email);
        if ($tenantId !== null) $studentQuery->where('tenant_id', $tenantId);
        $student = $studentQuery->first();
        if (!$student) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        $passwordIsModernHash = password_verify($password, $student->password);
        $passwordIsLegacyHash = hash_equals(strtolower($student->password), hash('sha256', $password));
        if (!$passwordIsModernHash && !$passwordIsLegacyHash) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        if (!$passwordIsModernHash) {
            $student->password = password_hash($password, PASSWORD_DEFAULT);
            $student->save();
        }

        $token = $this->jwt->generate($student->id, $student->email, $student->full_name, $tenantId);

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
                $tenantId = $this->tenantId($request);
                $studentQuery = Students::where('id', $payload['sub']);
                if ($tenantId !== null) $studentQuery->where('tenant_id', $tenantId);
                if (!empty($payload['tenant_id'])) $studentQuery->where('tenant_id', $payload['tenant_id']);
                $student = $studentQuery->first();
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
