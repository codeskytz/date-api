<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\ApiToken;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Authentication
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Simple hardcoded admin credentials for now
        if ($data['email'] === 'admin@dateapi.com' && $data['password'] === 'admin123456') {
            return response()->json([
                'status' => 'success',
                'message' => 'Admin login successful',
                'token' => 'admin-token-' . hash('sha256', $data['email'])
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid admin credentials'
        ], 401);
    }

    // Dashboard - Platform Statistics
    public function dashboard()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $totalPosts = Post::count();
        $bannedUsers = User::where('is_banned', true)->count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'banned_users' => $bannedUsers,
                'total_posts' => $totalPosts,
                'new_users_this_month' => $newUsersThisMonth,
                'platform_health' => [
                    'user_growth_rate' => $newUsersThisMonth,
                    'active_user_percentage' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0,
                ]
            ]
        ], 200);
    }

    // User Management
    public function listUsers(Request $request)
    {
        $perPage = $request->query('limit', 20);
        $page = $request->query('page', 1);
        $sortBy = $request->query('sort_by', 'created_at');
        $order = $request->query('order', 'desc');
        $filter = $request->query('filter', 'all'); // all, active, banned

        $query = User::query();

        if ($filter === 'banned') {
            $query->where('is_banned', true);
        } elseif ($filter === 'active') {
            $query->where('is_active', true)->where('is_banned', false);
        }

        $users = $query->orderBy($sortBy, $order)
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data' => collect($users->items())->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'is_banned' => $user->is_banned,
                    'created_at' => $user->created_at,
                    'last_login' => $user->last_login,
                ];
            })->all(),
            'pagination' => [
                'total' => $users->total(),
                'page' => $page,
                'limit' => $perPage,
                'pages' => $users->lastPage()
            ]
        ]);
    }

    public function getUserDetails($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'bio' => $user->bio,
                'is_active' => $user->is_active,
                'is_banned' => $user->is_banned,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'last_login' => $user->last_login,
                'posts_count' => $user->posts()->count(),
                'followers_count' => $user->followers()->count(),
                'following_count' => $user->following()->count(),
            ]
        ]);
    }

    public function banUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->is_banned) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already banned'
            ], 400);
        }

        $user->update(['is_banned' => true, 'is_active' => false]);

        return response()->json([
            'status' => 'success',
            'message' => 'User banned successfully',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'is_banned' => true
            ]
        ]);
    }

    public function unbanUser($id)
    {
        $user = User::findOrFail($id);

        if (!$user->is_banned) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not banned'
            ], 400);
        }

        $user->update(['is_banned' => false, 'is_active' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'User unbanned successfully',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'is_banned' => false
            ]
        ]);
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Delete user's posts first
            $user->posts()->delete();

            // Delete user's stories
            $user->stories()->delete();

            // Delete user's API tokens
            $user->tokens()->delete();

            // Delete user (this will trigger the deleting event)
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateUser($id, Request $request)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|string|max:20',
            'bio' => 'sometimes|string|max:500',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    // Post Management
    public function listPosts(Request $request)
    {
        $perPage = $request->query('limit', 20);
        $page = $request->query('page', 1);

        $posts = Post::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data' => collect($posts->items())->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'user_id' => $post->user_id,
                    'username' => $post->user->username,
                    'created_at' => $post->created_at,
                    'is_flagged' => $post->is_flagged ?? false,
                ];
            })->all(),
            'pagination' => [
                'total' => $posts->total(),
                'page' => $page,
                'limit' => $perPage,
                'pages' => $posts->lastPage()
            ]
        ]);
    }

    public function getPostDetails($id)
    {
        $post = Post::with('user')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'user_id' => $post->user_id,
                'username' => $post->user->username,
                'is_flagged' => $post->is_flagged ?? false,
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ]
        ]);
    }

    public function deletePost($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Post deleted successfully'
        ]);
    }

    public function flagPost($id, Request $request)
    {
        $post = Post::findOrFail($id);

        $data = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $post->update([
            'is_flagged' => true,
            'flag_reason' => $data['reason'],
            'flagged_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Post flagged successfully'
        ]);
    }

    public function unflagPost($id)
    {
        $post = Post::findOrFail($id);

        if (!$post->is_flagged) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post is not flagged'
            ], 400);
        }

        $post->update([
            'is_flagged' => false,
            'flag_reason' => null,
            'flagged_at' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Post unflagged successfully'
        ]);
    }

    // Platform Statistics
    public function getStatistics(Request $request)
    {
        $period = $request->query('period', 'month'); // day, week, month, year
        $startDate = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $newUsers = User::where('created_at', '>=', $startDate)->count();
        $newPosts = Post::where('created_at', '>=', $startDate)->count();
        $activeUsersCount = User::where('is_active', true)->count();

        return response()->json([
            'status' => 'success',
            'period' => $period,
            'data' => [
                'new_users' => $newUsers,
                'new_posts' => $newPosts,
                'active_users' => $activeUsersCount,
                'total_users' => User::count(),
                'total_posts' => Post::count(),
            ]
        ]);
    }

    // Moderation
    public function getFlaggedContent(Request $request)
    {
        $perPage = $request->query('limit', 20);
        $page = $request->query('page', 1);

        $flaggedPosts = Post::where('is_flagged', true)
            ->with('user')
            ->orderBy('flagged_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data' => collect($flaggedPosts->items())->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'username' => $post->user->username,
                    'reason' => $post->flag_reason,
                    'flagged_at' => $post->flagged_at,
                ];
            })->all(),
            'pagination' => [
                'total' => $flaggedPosts->total(),
                'page' => $page,
                'limit' => $perPage,
                'pages' => $flaggedPosts->lastPage()
            ]
        ]);
    }

    // System Management
    public function getSystemSettings()
    {
        $appName = Setting::get('app_name', config('app.name'));
        $appUrl = Setting::get('app_url', config('app.url'));

        return response()->json([
            'status' => 'success',
            'data' => [
                'app_name' => $appName,
                'app_env' => config('app.env'),
                'app_debug' => config('app.debug'),
                'app_url' => $appUrl,
            ]
        ]);
    }

    public function updateSystemSettings(Request $request)
    {
        try {
            // Only allow updating specific settings, ignore extra fields
            $allowedSettings = ['app_name', 'app_url'];
            $data = $request->only($allowedSettings);

            // Save each setting to database
            foreach ($data as $key => $value) {
                Setting::set($key, $value);
            }

            // Return updated settings
            $appName = Setting::get('app_name', config('app.name'));
            $appUrl = Setting::get('app_url', config('app.url'));

            return response()->json([
                'status' => 'success',
                'message' => 'Settings updated successfully',
                'data' => [
                    'app_name' => $appName,
                    'app_env' => config('app.env'),
                    'app_debug' => config('app.debug'),
                    'app_url' => $appUrl,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActivityLog(Request $request)
    {
        $userId = $request->query('user_id');
        $perPage = $request->query('limit', 20);
        $page = $request->query('page', 1);

        $query = User::query();
        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->orderBy('last_login', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'data' => collect($users->items())->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'last_login' => $user->last_login,
                    'created_at' => $user->created_at,
                ];
            })->all(),
            'pagination' => [
                'total' => $users->total(),
                'page' => $page,
                'limit' => $perPage,
                'pages' => $users->lastPage()
            ]
        ]);
    }
}
