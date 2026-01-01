<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Post;
use App\Models\Like;

class PostController extends Controller
{
    /**
     * Create a new post
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string|max:5000',
                'image' => 'nullable|url|max:500',
                'video' => 'nullable|url|max:500',
            ]);

            $user = auth('token')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Validate that media URLs belong to this user (security check)
            if (isset($validated['image']) && $validated['image']) {
                if (strpos($validated['image'], '/' . $user->id . '/') === false) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid image URL - must belong to authenticated user'
                    ], 422);
                }
            }

            if (isset($validated['video']) && $validated['video']) {
                if (strpos($validated['video'], '/' . $user->id . '/') === false) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid video URL - must belong to authenticated user'
                    ], 422);
                }
            }

            // Create the post
            $post = Post::create([
                'user_id' => $user->id,
                'description' => $validated['content'],
                'image' => $validated['image'] ?? null,
                'video' => $validated['video'] ?? null,
                'likes_count' => 0,
                'comments_count' => 0,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Post created successfully',
                'data' => [
                    'id' => $post->id,
                    'user_id' => $post->user_id,
                    'content' => $post->description,
                    'image' => $post->image,
                    'video' => $post->video,
                    'likes_count' => $post->likes_count,
                    'comments_count' => $post->comments_count,
                    'created_at' => $post->created_at->toIso8601String(),
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feed of posts
     */
    public function feed(Request $request)
    {
        try {
            $user = auth('token')->user();
            $page = $request->query('page', 1);
            $perPage = $request->query('limit', 20);

            // Get posts from followed users + own posts
            $query = Post::with('user:id,name,username,avatar,verified')
                ->where('is_flagged', false)
                ->orderByDesc('created_at');

            // If user is authenticated, show posts from followed users + own posts
            if ($user) {
                $followingIds = $user->following()->pluck('follows.followed_id')->toArray();
                $userIds = array_merge($followingIds, [$user->id]);
                $query->whereIn('user_id', $userIds);
            }

            $posts = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $posts->map(function ($post) use ($user) {
                return [
                    'id' => $post->id,
                    'user' => [
                        'id' => $post->user->id,
                        'username' => $post->user->username,
                        'name' => $post->user->name,
                        'avatar' => $post->user->avatar,
                        'verified' => $post->user->verified ?? false,
                    ],
                    'content' => $post->description,
                    'image' => $post->image,
                    'video' => $post->video,
                    'likes_count' => $post->likes_count,
                    'comments_count' => $post->comments_count,
                    'is_liked' => $user ? $post->isLikedBy($user) : false,
                    'created_at' => $post->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'pagination' => [
                    'page' => $posts->currentPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                    'pages' => $posts->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch feed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Like or unlike a post
     */
    public function like($id)
    {
        try {
            $user = auth('token')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $post = Post::find($id);
            if (!$post) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Post not found'
                ], 404);
            }

            // Check if already liked
            $existingLike = Like::where('user_id', $user->id)
                ->where('post_id', $post->id)
                ->first();

            if ($existingLike) {
                // Unlike
                $existingLike->delete();
                $post->decrement('likes_count');
                $liked = false;
            } else {
                // Like
                Like::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
                $post->increment('likes_count');
                $liked = true;
            }

            return response()->json([
                'status' => 'success',
                'liked' => $liked,
                'total_likes' => $post->fresh()->likes_count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to like/unlike post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Comment on a post (stub for now)
     */
    public function comment(Request $request, $id)
    {
        return response()->json(['comment_id' => null, 'message' => 'Comment added (stub)']);
    }

    /**
     * Save a post (stub for now)
     */
    public function save($id)
    {
        return response()->json(['saved' => true]);
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

        $posts = Post::where('is_flagged', false)
            ->where(function ($q) use ($query) {
                $q->where('content', 'like', "%$query%")
                  ->orWhere('media', 'like', "%$query%");
            })
            ->with('user:id,name,username,avatar')
            ->select('id', 'user_id', 'content', 'image', 'video', 'likes_count', 'comments_count', 'created_at')
            ->limit($limit)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content ?? 'Shared a post',
                    'image' => $post->image,
                    'video' => $post->video,
                    'likes' => $post->likes_count,
                    'comments' => $post->comments_count,
                    'created_at' => $post->created_at->toIso8601String(),
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                        'username' => $post->user->username,
                        'avatar' => $post->user->avatar,
                    ],
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => count($posts) > 0 ? 'Search results found' : 'No posts found matching your search',
            'data' => $posts,
            'total' => count($posts)
        ]);
    }

    /**
     * Get current user's posts
     */
    public function myPosts(Request $request)
    {
        try {
            $user = auth('token')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $perPage = $request->query('limit', 20);
            $page = $request->query('page', 1);

            $posts = Post::where('user_id', $user->id)
                ->where('is_flagged', false)
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            $data = $posts->map(function ($post) use ($user) {
                return [
                    'id' => $post->id,
                    'user_id' => $post->user_id,
                    'content' => $post->description,
                    'image' => $post->image,
                    'video' => $post->video,
                    'likes_count' => $post->likes_count,
                    'comments_count' => $post->comments_count,
                    'is_liked' => $post->isLikedBy($user),
                    'created_at' => $post->created_at->toIso8601String()
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'pagination' => [
                    'page' => $posts->currentPage(),
                    'limit' => $posts->perPage(),
                    'total' => $posts->total(),
                    'pages' => $posts->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch posts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a single post
     */
    public function show($id)
    {
        try {
            $user = auth('token')->user();
            
            $post = Post::with('user:id,name,username,avatar,verified')
                ->where('is_flagged', false)
                ->find($id);

            if (!$post) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Post not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $post->id,
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                        'username' => $post->user->username,
                        'avatar' => $post->user->avatar,
                        'verified' => $post->user->verified ?? false,
                    ],
                    'content' => $post->description,
                    'image' => $post->image,
                    'video' => $post->video,
                    'likes_count' => $post->likes_count,
                    'comments_count' => $post->comments_count,
                    'is_liked' => $user ? $post->isLikedBy($user) : false,
                    'created_at' => $post->created_at->toIso8601String()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get posts by username
     */
    public function userPosts($username)
    {
        try {
            $currentUser = auth('token')->user();
            $perPage = request()->query('limit', 20);
            $page = request()->query('page', 1);

            // Find the user by username
            $user = \App\Models\User::where('username', $username)->first();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            // Get user's posts
            $posts = Post::where('user_id', $user->id)
                ->where('is_flagged', false)
                ->orderByDesc('created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            $data = $posts->map(function ($post) use ($currentUser) {
                return [
                    'id' => $post->id,
                    'user_id' => $post->user_id,
                    'content' => $post->description,
                    'image' => $post->image,
                    'video' => $post->video,
                    'likes_count' => $post->likes_count,
                    'comments_count' => $post->comments_count,
                    'is_liked' => $currentUser ? $post->isLikedBy($currentUser) : false,
                    'created_at' => $post->created_at->toIso8601String()
                ];
            });

            return response()->json([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'verified' => $user->verified ?? false,
                ],
                'data' => $data,
                'pagination' => [
                    'page' => $posts->currentPage(),
                    'limit' => $posts->perPage(),
                    'total' => $posts->total(),
                    'pages' => $posts->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch user posts: ' . $e->getMessage()
            ], 500);
        }
    }
}
