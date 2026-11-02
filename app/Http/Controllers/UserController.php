<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function create(Request $request)
    {
        $tenantId = $this->tenantId($request);
        $this->validate($request, [
            'email' => 'required|email',
            'name' => 'required|string',
            'level' => 'required|string',
        ]);

        $email = $request->input('email');
        $existingQuery = User::where('email', $email);
        if ($tenantId !== null) $existingQuery->where('tenant_id', $tenantId);
        if ($existingQuery->exists()) {
            return response("Email already exists", 409);
        }

        $userData = [
            'email' => $email,
            'name' => $request->input('name'),
            'level' => $request->input('level'),
        ];
        if ($tenantId !== null) $userData['tenant_id'] = $tenantId;
        $user = User::create($userData);

        return response()->json($user, 201, [
            'Location' => '/api/users/' . $user->id
        ]);
    }

    public function all(Request $request)
    {
        $query = User::query();
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        return response()->json($query->get());
    }

    public function byId(Request $request, $id)
    {
        $query = User::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $user = $query->first();
        if (!$user) {
            return response()->json(null, 404);
        }
        return response()->json($user);
    }

    public function byEmail(Request $request)
    {
        $email = $request->query('email');
        $query = User::where('email', $email);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $user = $query->first();
        if (!$user) {
            return response()->json(null, 404);
        }
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $query = User::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $user = $query->first();
        if (!$user) {
            return response()->json(null, 404);
        }

        $name = $request->input('name');
        $level = $request->input('level');

        if ($name !== null && trim($name) !== '') {
            $user->name = $name;
        }
        if ($level !== null && trim($level) !== '') {
            $user->level = $level;
        }

        $user->save();

        return response()->json($user);
    }

    public function delete(Request $request, $id)
    {
        $query = User::where('id', $id);
        $tenantId = $this->tenantId($request);
        if ($tenantId !== null) $query->where('tenant_id', $tenantId);
        $user = $query->first();
        if (!$user) {
            return response()->json(null, 404);
        }
        $user->delete();
        return response()->json(null, 204);
    }
}
