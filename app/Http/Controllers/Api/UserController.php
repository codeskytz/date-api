<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'bio' => $user->bio,
                'verified' => $user->is_verified,
                'followers' => $user->followers()->count(),
                'following' => $user->following()->count(),
            ]
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->query('q', '');
        $limit = $request->query('limit', 20);

        if (empty($query)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Please provide a search query',
                'data' => [],
                'total' => 0
            ]);
        }

        $users = User::where('is_banned', false)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%$query%")
                  ->orWhere('username', 'like', "%$query%")
                  ->orWhere('bio', 'like', "%$query%");
            })
            ->select('id', 'name', 'username', 'avatar', 'bio', 'verified')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'bio' => $user->bio,
                    'verified' => (bool) $user->verified,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Search results found',
            'data' => $users,
            'total' => count($users)
        ]);
    }

    public function show($username)
    {
        $user = User::where('username', $username)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'bio' => $user->bio,
                'avatar' => $user->avatar,
                'verified' => $user->is_verified,
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count(),
                'posts_count' => $user->posts()->count(),
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'bio' => 'nullable|string|max:500',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
        ]);

        if (!empty($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (!empty($validated['username'])) {
            $user->username = $validated['username'];
        }
        if (array_key_exists('bio', $validated)) {
            $user->bio = $validated['bio'];
        }
        if (!empty($validated['email'])) {
            $user->email = $validated['email'];
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar' => $user->avatar,
                'verified' => $user->is_verified,
            ]
        ]);
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
