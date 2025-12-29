<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(Request $request, $username)
    {
        $user = auth()->user();
        $targetUser = User::where('username', $username)->firstOrFail();

        if ($user->id === $targetUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot follow yourself'
            ], 400);
        }

        $follow = Follow::firstOrCreate([
            'follower_id' => $user->id,
            'followed_id' => $targetUser->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User followed successfully',
            'data' => [
                'follower_id' => $user->id,
                'followed_id' => $targetUser->id,
                'username' => $targetUser->username,
                'name' => $targetUser->name,
                'is_following' => true
            ]
        ]);
    }

    public function unfollow(Request $request, $username)
    {
        $user = auth()->user();
        $targetUser = User::where('username', $username)->firstOrFail();

        Follow::where([
            'follower_id' => $user->id,
            'followed_id' => $targetUser->id,
        ])->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User unfollowed successfully',
            'data' => [
                'follower_id' => $user->id,
                'followed_id' => $targetUser->id,
                'username' => $targetUser->username,
                'name' => $targetUser->name,
                'is_following' => false
            ]
        ]);
    }

    public function followers($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $perPage = request()->query('limit', 20);
        $page = request()->query('page', 1);

        $followers = $user->followers()
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'username' => $user->username,
            'data' => collect($followers->items())->map(function ($follower) {
                return [
                    'id' => $follower->id,
                    'name' => $follower->name,
                    'username' => $follower->username,
                    'avatar' => $follower->avatar,
                ];
            })->all(),
            'pagination' => [
                'page' => $page,
                'limit' => $perPage,
                'total' => $followers->total(),
                'pages' => $followers->lastPage()
            ]
        ]);
    }

    public function following($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $perPage = request()->query('limit', 20);
        $page = request()->query('page', 1);

        $following = $user->following()
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'username' => $user->username,
            'data' => collect($following->items())->map(function ($followedUser) {
                return [
                    'id' => $followedUser->id,
                    'name' => $followedUser->name,
                    'username' => $followedUser->username,
                    'avatar' => $followedUser->avatar,
                ];
            })->all(),
            'pagination' => [
                'page' => $page,
                'limit' => $perPage,
                'total' => $following->total(),
                'pages' => $following->lastPage()
            ]
        ]);
    }

    public function isFollowing($username)
    {
        $user = auth()->user();
        $targetUser = User::where('username', $username)->firstOrFail();

        $isFollowing = Follow::where([
            'follower_id' => $user->id,
            'followed_id' => $targetUser->id,
        ])->exists();

        return response()->json([
            'status' => 'success',
            'username' => $targetUser->username,
            'is_following' => $isFollowing
        ]);
    }

    public function myFollowers()
    {
        $user = auth()->user();
        $perPage = request()->query('limit', 20);
        $page = request()->query('page', 1);

        $followers = $user->followers()
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'username' => $user->username,
            'followers_count' => $followers->total(),
            'data' => collect($followers->items())->map(function ($follower) {
                return [
                    'id' => $follower->id,
                    'name' => $follower->name,
                    'username' => $follower->username,
                    'avatar' => $follower->avatar,
                ];
            })->all(),
            'pagination' => [
                'page' => $page,
                'limit' => $perPage,
                'total' => $followers->total(),
                'pages' => $followers->lastPage()
            ]
        ]);
    }

    public function myFollowing()
    {
        $user = auth()->user();
        $perPage = request()->query('limit', 20);
        $page = request()->query('page', 1);

        $following = $user->following()
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'username' => $user->username,
            'following_count' => $following->total(),
            'data' => collect($following->items())->map(function ($followedUser) {
                return [
                    'id' => $followedUser->id,
                    'name' => $followedUser->name,
                    'username' => $followedUser->username,
                    'avatar' => $followedUser->avatar,
                ];
            })->all(),
            'pagination' => [
                'page' => $page,
                'limit' => $perPage,
                'total' => $following->total(),
                'pages' => $following->lastPage()
            ]
        ]);
    }
}
