<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\Post;
use App\Models\Like;
use App\Models\Comment;
use App\Models\SavedPost;

class PostController extends Controller
{
    /**
     * Create a new post
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Post store request', [
                'all_data' => $request->all(),
                'headers' => $request->headers->all(),
            ]);
            
            $validated = $request->validate([
                'content' => 'required|string|max:5000',
                'image' => 'nullable|url|max:1000',
                'video' => 'nullable|url|max:1000',
                'thumbnail' => 'nullable|url|max:1000',
                'is_reel' => 'nullable|boolean',
            ]);

            \Log::info('Post validation passed', [
                'content_length' => strlen($validated['content'] ?? ''),
                'has_video' => !empty($validated['video']),
                'video_length' => strlen($validated['video'] ?? ''),
                'has_thumbnail' => !empty($validated['thumbnail']),
                'thumbnail_length' => strlen($validated['thumbnail'] ?? ''),
            ]);

            $user = auth()->user();
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
            $media = [];
            if (!empty($validated['image'])) {
                $media['image'] = $validated['image'];
            }
            if (!empty($validated['video'])) {
                $media['video'] = $validated['video'];
            }

            $post = Post::create([
                'user_id' => $user->id,
                'content' => $validated['content'],
                'media' => $media ?: null,
                'image' => $validated['image'] ?? null,
                'video' => $validated['video'] ?? null,
                'thumbnail' => $validated['thumbnail'] ?? null,
                'is_reel' => $validated['is_reel'] ? true : false,
                'likes_count' => 0,
                'comments_count' => 0,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Post created successfully',
                'data' => [
                    'id' => $post->id,
                    'user_id' => $post->user_id,
                    'content' => $post->content,
                    'image' => is_array($post->media ?? null) ? ($post->media['image'] ?? null) : null,
                    'video' => is_array($post->media ?? null) ? ($post->media['video'] ?? null) : null,
                    'likes_count' => $post->likes_count,
                    'comments_count' => $post->comments_count,
                    'created_at' => $post->created_at->toIso8601String(),
                ]
            ], 201);
        } catch (ValidationException $e) {
            \Log::error('Post validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Post creation failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
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
            $user = auth()->user();
            $page = $request->query('page', 1);
            $perPage = $request->query('limit', 20);

            // Get posts from followed users + own posts
            $query = Post::with('user:id,name,username,avatar,verified')
                ->where('is_flagged', false)
                ->orderByDesc('created_at');

            // If user is authenticated, show posts from followed users + own posts
            if ($user) {
                $followingIds = $user->following()->pluck('id')->toArray();
                $userIds = array_merge($followingIds, [$user->id]);
                
                // Only filter by user_ids if we have at least one user
                if (!empty($userIds)) {
                    $query->whereIn('user_id', $userIds);
                } else {
                    // If user has no following, show only their own posts
                    $query->where('user_id', $user->id);
                }
            }

            $posts = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $posts->map(function ($post) use ($user) {
                // Skip posts with deleted users
                if (!$post->user) {
                    return null;
                }
                
                // Extract media URLs and ensure they are presigned
                $image = is_array($post->media ?? null) ? ($post->media['image'] ?? null) : ($post->image ?? null);
                $video = is_array($post->media ?? null) ? ($post->media['video'] ?? null) : ($post->video ?? null);
                $thumbnail = $post->thumbnail ?? null;
                
                return [
                    'id' => $post->id,
                    'user' => [
                        'id' => $post->user->id,
                        'username' => $post->user->username,
                        'name' => $post->user->name,
                        'avatar' => $post->user->avatar,
                        'verified' => $post->user->verified ?? false,
                    ],
                    'content' => $post->content,
                    'image' => $image,
                    'video' => $video,
                    'thumbnail' => $thumbnail,
                    'likes_count' => $post->likes_count ?? 0,
                    'comments_count' => $post->comments_count ?? 0,
                    'is_liked' => $user ? $post->isLikedBy($user) : false,
                    'created_at' => $post->created_at->toIso8601String(),
                ];
            })->filter(); // Remove null entries

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
            \Log::error('Feed error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            
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
            $user = auth()->user();
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
     * Get comments for a post
     */
    public function getComments($id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Post not found'
                ], 404);
            }

            $comments = Comment::with(['user:id,name,username,avatar,verified', 'replies.user:id,name,username,avatar,verified'])
                ->where('post_id', $id)
                ->whereNull('parent_id')
                ->orderByDesc('created_at')
                ->get();

            $data = $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'user' => [
                        'id' => $comment->user->id,
                        'username' => $comment->user->username,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar,
                        'verified' => $comment->user->verified ?? false,
                    ],
                    'content' => $comment->content,
                    'likes_count' => $comment->likes_count,
                    'replies_count' => $comment->replies()->count(),
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'user' => [
                                'id' => $reply->user->id,
                                'username' => $reply->user->username,
                                'name' => $reply->user->name,
                                'avatar' => $reply->user->avatar,
                                'verified' => $reply->user->verified ?? false,
                            ],
                            'content' => $reply->content,
                            'likes_count' => $reply->likes_count,
                            'created_at' => $reply->created_at->toIso8601String(),
                        ];
                    }),
                    'created_at' => $comment->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'total' => $comments->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch comments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Comment on a post
     */
    public function comment(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string|max:1000',
                'parent_id' => 'nullable|exists:comments,id',
            ]);

            $user = auth()->user();
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

            $comment = Comment::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'content' => $validated['content'],
            ]);

            $post->increment('comments_count');

            $comment->load('user:id,name,username,avatar,verified');

            return response()->json([
                'status' => 'success',
                'message' => 'Comment added successfully',
                'data' => [
                    'id' => $comment->id,
                    'user' => [
                        'id' => $comment->user->id,
                        'username' => $comment->user->username,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar,
                        'verified' => $comment->user->verified ?? false,
                    ],
                    'content' => $comment->content,
                    'likes_count' => $comment->likes_count,
                    'created_at' => $comment->created_at->toIso8601String(),
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
                'message' => 'Failed to add comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save or unsave a post
     */
    public function save($id)
    {
        try {
            $user = auth()->user();
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

            $savedPost = SavedPost::where('user_id', $user->id)
                ->where('post_id', $post->id)
                ->first();

            if ($savedPost) {
                // Unsave
                $savedPost->delete();
                $saved = false;
            } else {
                // Save
                SavedPost::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
                $saved = true;
            }

            return response()->json([
                'status' => 'success',
                'saved' => $saved,
                'message' => $saved ? 'Post saved' : 'Post unsaved'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save/unsave post: ' . $e->getMessage()
            ], 500);
        }
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
            ->select('id', 'user_id', 'content', 'likes_count', 'comments_count', 'created_at')
            ->limit($limit)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content ?? 'Shared a post',
                    'image' => is_array($post->media ?? null) ? ($post->media['image'] ?? null) : null,
                    'video' => is_array($post->media ?? null) ? ($post->media['video'] ?? null) : null,
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
            $user = auth()->user();
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
                    'content' => $post->content,
                    'image' => is_array($post->media ?? null) ? ($post->media['image'] ?? null) : null,
                    'video' => is_array($post->media ?? null) ? ($post->media['video'] ?? null) : null,
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
            $user = auth()->user();
            
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
                    'content' => $post->content,
                    'image' => is_array($post->media ?? null) ? ($post->media['image'] ?? null) : null,
                    'video' => is_array($post->media ?? null) ? ($post->media['video'] ?? null) : null,
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
            $currentUser = auth()->user();
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
                    'content' => $post->content,
                    'image' => is_array($post->media ?? null) ? ($post->media['image'] ?? null) : null,
                    'video' => is_array($post->media ?? null) ? ($post->media['video'] ?? null) : null,
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
