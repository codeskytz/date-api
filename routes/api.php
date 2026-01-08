<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OAuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\StoryController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\DiscoverController;
use App\Http\Controllers\Api\ConversationController;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth.token');

    // Media Upload (Protected)
    Route::middleware('auth.token')->group(function () {
        Route::post('media/post-image', [MediaController::class, 'uploadPostImage']);
        Route::post('media/post-video', [MediaController::class, 'uploadPostVideo']);
        Route::post('media/avatar', [MediaController::class, 'uploadAvatar']);
        Route::post('media/cover', [MediaController::class, 'uploadCoverImage']);
        Route::post('media/story', [MediaController::class, 'uploadStory']);
        Route::post('media/story-video', [MediaController::class, 'uploadStoryVideo']);
        Route::post('media/delete', [MediaController::class, 'deleteFile']);
    });

    // Verification Endpoints (Protected)
    Route::middleware('auth.token')->group(function () {
        Route::post('verification/request', [VerificationController::class, 'request']);
        Route::get('verification/status', [VerificationController::class, 'status']);
        Route::delete('verification/cancel', [VerificationController::class, 'cancel']);
    });

    // OTP Verification
    Route::post('otp/send', [OtpController::class, 'send']);
    Route::post('otp/verify', [OtpController::class, 'verify']);
    Route::post('otp/resend', [OtpController::class, 'resend']);
    Route::get('otp/status', [OtpController::class, 'checkStatus']);

    // OAuth
    Route::get('oauth/{provider}/redirect', [OAuthController::class, 'redirect']);
    Route::get('oauth/{provider}/callback', [OAuthController::class, 'callback']);
    Route::post('oauth/{provider}/token', [OAuthController::class, 'token']);

    // User (protected)
    Route::middleware('auth.token')->group(function () {
        Route::get('me', [UserController::class, 'me']);
        Route::put('me', [UserController::class, 'update']);
        Route::get('account/privacy', [UserController::class, 'privacy']);
        Route::post('account/privacy', [UserController::class, 'updatePrivacy']);

        // Posts
        Route::get('my-posts', [PostController::class, 'myPosts']);
        Route::post('posts', [PostController::class, 'store']);
        // Reels (reuse posts table with is_reel flag)
        Route::post('reels', [PostController::class, 'store']);
        Route::post('posts/{id}/like', [PostController::class, 'like']);
        Route::get('posts/{id}/comments', [PostController::class, 'getComments']);
        Route::post('posts/{id}/comments', [PostController::class, 'comment']);
        Route::post('posts/{id}/save', [PostController::class, 'save']);

        // Stories
        Route::post('stories', [StoryController::class, 'store']);
        Route::post('stories/{id}/view', [StoryController::class, 'view']);

        // Matches
        Route::get('matches', [MatchController::class, 'index']);
        Route::get('users/{id}/match-status', [MatchController::class, 'status']);

        // Messages
        Route::post('messages', [MessageController::class, 'store']);
        Route::get('messages/{user_id}', [MessageController::class, 'index']);

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/settings', [NotificationController::class, 'settings']);
        Route::post('notifications/settings', [NotificationController::class, 'updateSettings']);

        // Follow
        Route::post('users/{username}/follow', [FollowController::class, 'follow']);
        Route::post('users/{username}/unfollow', [FollowController::class, 'unfollow']);
        Route::get('users/{username}/is-following', [FollowController::class, 'isFollowing']);
        
        // My followers/following
        Route::get('my/followers', [FollowController::class, 'myFollowers']);
        Route::get('my/following', [FollowController::class, 'myFollowing']);
    });

    // Public routes
    Route::get('posts/{id}', [PostController::class, 'show']);
    Route::get('search/posts', [PostController::class, 'search']);
    Route::get('search/users', [UserController::class, 'search']);
    Route::get('users/{username}', [UserController::class, 'show']);
    Route::get('users/{username}/posts', [PostController::class, 'userPosts']);
    Route::get('users/{username}/followers', [FollowController::class, 'followers']);
    Route::get('users/{username}/following', [FollowController::class, 'following']);
    Route::get('feed', [PostController::class, 'feed']);


    // Stories feed (public)
    Route::get('stories/feed', [StoryController::class, 'feed']);
    // Admin Routes
    Route::prefix('admin')->group(function () {
        // Admin Authentication (Public)
        Route::post('login', [AdminController::class, 'login']);

        // Admin Dashboard & Statistics (Protected - in real app)
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        Route::get('statistics', [AdminController::class, 'getStatistics']);
        Route::get('settings', [AdminController::class, 'getSystemSettings']);
        Route::put('settings', [AdminController::class, 'updateSystemSettings']);

        // User Management
        Route::get('users', [AdminController::class, 'listUsers']);
        Route::get('users/{id}', [AdminController::class, 'getUserDetails']);
        Route::put('users/{id}', [AdminController::class, 'updateUser']);
        Route::post('users/{id}/ban', [AdminController::class, 'banUser']);
        Route::post('users/{id}/unban', [AdminController::class, 'unbanUser']);
        Route::delete('users/{id}', [AdminController::class, 'deleteUser']);

        // Post Management
        Route::get('posts', [AdminController::class, 'listPosts']);
        Route::get('posts/{id}', [AdminController::class, 'getPostDetails']);
        Route::delete('posts/{id}', [AdminController::class, 'deletePost']);
        Route::post('posts/{id}/flag', [AdminController::class, 'flagPost']);
        Route::post('posts/{id}/unflag', [AdminController::class, 'unflagPost']);

        // Discover
        Route::get('discover/users', [DiscoverController::class, 'users']);
        Route::get('discover/nearby', [DiscoverController::class, 'nearby']);

        // Conversations
        Route::get('conversations', [ConversationController::class, 'index']);
        
        // Content Moderation
        Route::get('flagged-content', [AdminController::class, 'getFlaggedContent']);

        // Activity Log
        Route::get('activity-log', [AdminController::class, 'getActivityLog']);

        // Verification Management
        Route::get('verification/pending', [AdminController::class, 'listPendingVerifications']);
        Route::get('verification/approved', [AdminController::class, 'listApprovedVerifications']);
        Route::get('verification/rejected', [AdminController::class, 'listRejectedVerifications']);
        Route::get('verification/all', [AdminController::class, 'listAllVerifications']);
        Route::get('verification/{id}', [AdminController::class, 'getVerificationDetails']);
        Route::post('verification/{id}/approve', [AdminController::class, 'approveVerification']);
        Route::post('verification/{id}/reject', [AdminController::class, 'rejectVerification']);
    });
});
