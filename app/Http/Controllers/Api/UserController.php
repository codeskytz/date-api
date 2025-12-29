<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar' => $user->avatar,
            'followers' => 0,
            'following' => 0,
        ]);
    }

    public function show($username)
    {
        return response()->json(['username' => $username, 'message' => 'User profile (stub)']);
    }

    public function update(Request $request)
    {
        return response()->json(['message' => 'Update profile (stub)']);
    }

    public function privacy()
    {
        return response()->json(['privacy' => 'public', 'message' => 'Privacy settings (stub)']);
    }

    public function updatePrivacy(Request $request)
    {
        return response()->json(['message' => 'Update privacy (stub)']);
    }
}
