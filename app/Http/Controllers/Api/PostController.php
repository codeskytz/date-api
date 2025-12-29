<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request)
    {
        return response()->json(['post_id' => null, 'message' => 'Create post (stub)'], 201);
    }

    public function feed(Request $request)
    {
        return response()->json(['page' => 1, 'data' => [], 'message' => 'Feed (stub)']);
    }

    public function like($id)
    {
        return response()->json(['liked' => true, 'total_likes' => 1]);
    }

    public function comment(Request $request, $id)
    {
        return response()->json(['comment_id' => null, 'message' => 'Comment added (stub)']);
    }

    public function save($id)
    {
        return response()->json(['saved' => true]);
    }

    public function myPosts(Request $request)
    {
        $user = auth()->user();
        $perPage = $request->query('limit', 20);
        $page = $request->query('page', 1);

        return response()->json([
            'status' => 'success',
            'data' => [
                [
                    'id' => 1,
                    'user_id' => $user->id,
                    'content' => 'My first post',
                    'image' => null,
                    'location' => null,
                    'likes' => 0,
                    'comments' => 0,
                    'created_at' => now()->toIso8601String()
                ]
            ],
            'pagination' => [
                'page' => $page,
                'limit' => $perPage,
                'total' => 1,
                'pages' => 1
            ]
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $id,
                'user_id' => 1,
                'user' => [
                    'id' => 1,
                    'name' => 'John Doe',
                    'username' => 'johndoe',
                    'avatar' => null
                ],
                'content' => 'Sample post content',
                'image' => null,
                'location' => null,
                'likes' => 5,
                'comments' => 2,
                'created_at' => now()->toIso8601String()
            ]
        ]);
    }

    public function userPosts($username)
    {
        $perPage = request()->query('limit', 20);
        $page = request()->query('page', 1);

        return response()->json([
            'status' => 'success',
            'user' => [
                'username' => $username,
                'name' => 'User Name',
                'avatar' => null
            ],
            'data' => [
                [
                    'id' => 1,
                    'user_id' => 1,
                    'content' => 'User public post',
                    'image' => null,
                    'location' => null,
                    'likes' => 3,
                    'comments' => 1,
                    'created_at' => now()->toIso8601String()
                ]
            ],
            'pagination' => [
                'page' => $page,
                'limit' => $perPage,
                'total' => 1,
                'pages' => 1
            ]
        ]);
    }
}
