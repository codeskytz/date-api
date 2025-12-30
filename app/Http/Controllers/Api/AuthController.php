<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\ApiToken;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $plain = Str::random(60);
        $hashed = hash('sha256', $plain);
        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => 'default',
            'token' => $hashed,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Account created successfully', 'token' => $plain], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
        }

        $plain = Str::random(60);
        $hashed = hash('sha256', $plain);
        $token = ApiToken::create([
            'user_id' => $user->id,
            'name' => 'default',
            'token' => $hashed,
        ]);

        return response()->json(['status' => 'success', 'token' => $plain, 'user' => ['id' => $user->id, 'username' => $user->username]], 200);
    }

    public function logout(Request $request)
    {
        $user = auth()->user();
        
        // Get the token from the request
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'No token provided'
            ], 401);
        }

        // Hash the token to find it in the database
        $hashedToken = hash('sha256', $token);
        
        // Delete the API token
        $deleted = ApiToken::where('user_id', $user->id)
            ->where('token', $hashedToken)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }
}