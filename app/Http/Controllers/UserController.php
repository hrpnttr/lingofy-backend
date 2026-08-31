<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function create(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'name' => 'required|string',
            'level' => 'required|string',
        ]);

        $email = $request->input('email');
        if (User::where('email', $email)->exists()) {
            return response("Email already exists", 409);
        }

        $user = User::create([
            'email' => $email,
            'name' => $request->input('name'),
            'level' => $request->input('level'),
        ]);

        return response()->json($user, 201, [
            'Location' => '/api/users/' . $user->id
        ]);
    }

    public function all()
    {
        return response()->json(User::all());
    }

    public function byId($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(null, 404);
        }
        return response()->json($user);
    }

    public function byEmail(Request $request)
    {
        $email = $request->query('email');
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(null, 404);
        }
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
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

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(null, 404);
        }
        $user->delete();
        return response()->json(null, 204);
    }
}
