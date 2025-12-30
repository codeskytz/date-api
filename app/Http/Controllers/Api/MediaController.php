<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{
    public function __construct(protected MediaStorageService $mediaService)
    {
    }

    /**
     * Upload a post image
     */
    public function uploadPostImage(Request $request)
    {
        try {
            $validated = $request->validate([
                'image' => 'required|image|max:5120', // 5MB max
            ]);

            $user = auth('token')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $imageUrl = $this->mediaService->uploadPostImage($request->file('image'), $user->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Image uploaded successfully',
                'data' => [
                    'url' => $imageUrl,
                    'size' => $request->file('image')->getSize(),
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request)
    {
        try {
            $validated = $request->validate([
                'avatar' => 'required|image|max:2048', // 2MB max for avatar
            ]);

            $user = auth('token')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $avatarUrl = $this->mediaService->uploadAvatar($request->file('avatar'), $user->id);

            // Update user avatar
            $user->update(['avatar' => $avatarUrl]);

            return response()->json([
                'status' => 'success',
                'message' => 'Avatar uploaded successfully',
                'data' => [
                    'url' => $avatarUrl,
                    'user_id' => $user->id,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload cover image
     */
    public function uploadCoverImage(Request $request)
    {
        try {
            $validated = $request->validate([
                'cover' => 'required|image|max:5120', // 5MB max
            ]);

            $user = auth('token')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $coverUrl = $this->mediaService->uploadCoverImage($request->file('cover'), $user->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Cover image uploaded successfully',
                'data' => [
                    'url' => $coverUrl,
                    'user_id' => $user->id,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload cover image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload story image
     */
    public function uploadStory(Request $request)
    {
        try {
            $validated = $request->validate([
                'image' => 'required|image|max:5120',
                'caption' => 'nullable|string|max:500',
                'duration' => 'nullable|integer|min:3600|max:86400', // 1 hour to 24 hours
            ]);

            $user = auth('token')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $imageUrl = $this->mediaService->uploadStoryImage($request->file('image'), $user->id);

            // Create story record
            $story = $user->stories()->create([
                'image' => $imageUrl,
                'caption' => $request->input('caption'),
                'expires_at' => now()->addSeconds($request->input('duration', 86400)), // Default 24 hours
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Story uploaded successfully',
                'data' => [
                    'id' => $story->id,
                    'url' => $imageUrl,
                    'expires_in' => $story->getRemainingTime(),
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload story: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete file from S3
     */
    public function uploadPostVideo(Request $request)
    {
        try {
            $validated = $request->validate([
                'video' => 'required|mimes:mp4,webm,mov,avi,mkv|max:102400', // 100MB max
            ]);

            $user = auth('token')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $videoFile = $request->file('video');
            
            if (!$this->mediaService->isVideo($videoFile)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File must be a video'
                ], 422);
            }

            if (!$this->mediaService->validateVideoSize($videoFile)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Video size exceeds 100MB limit'
                ], 422);
            }

            $videoUrl = $this->mediaService->uploadPostVideo($videoFile, $user->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Video uploaded successfully',
                'data' => [
                    'url' => $videoUrl,
                    'size' => $videoFile->getSize(),
                    'status' => 'pending' // Video processing status
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload video: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload story video
     */
    public function uploadStoryVideo(Request $request)
    {
        try {
            $validated = $request->validate([
                'video' => 'required|mimes:mp4,webm,mov,avi,mkv|max:102400', // 100MB max
                'caption' => 'nullable|string|max:500',
                'duration' => 'nullable|integer|min:5|max:43200', // 5 min to 30 days
            ]);

            $user = auth('token')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $videoFile = $request->file('video');
            
            if (!$this->mediaService->isVideo($videoFile)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File must be a video'
                ], 422);
            }

            if (!$this->mediaService->validateVideoSize($videoFile)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Video size exceeds 100MB limit'
                ], 422);
            }

            $videoUrl = $this->mediaService->uploadStoryVideo($videoFile, $user->id);
            
            // Calculate expiration time (default 24 hours)
            $duration = $request->input('duration', 1440); // minutes
            $expiresAt = now()->addMinutes($duration);

            // Create story record
            $story = $user->stories()->create([
                'video' => $videoUrl,
                'media_type' => 'video',
                'caption' => $request->input('caption'),
                'expires_at' => $expiresAt,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Video story created successfully',
                'data' => [
                    'id' => $story->id,
                    'url' => $videoUrl,
                    'media_type' => 'video',
                    'expires_at' => $expiresAt,
                    'size' => $videoFile->getSize(),
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload video story: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete file from S3
     */
    public function deleteFile(Request $request)
    {
        try {
            $validated = $request->validate([
                'url' => 'required|url',
            ]);

            $user = auth('token')->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Verify the file belongs to the user
            $url = $request->input('url');
            if (strpos($url, '/' . $user->id . '/') === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to delete this file'
                ], 403);
            }

            $deleted = $this->mediaService->deleteFile($url);

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'File deleted successfully'
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'File not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete file: ' . $e->getMessage()
            ], 500);
        }
    }
}
