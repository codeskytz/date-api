<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Services\MediaStorageService;
use App\Services\VideoThumbnailService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{
    public function __construct(
        protected MediaStorageService $mediaService,
        protected VideoThumbnailService $thumbnailService
    ) {
    }

    /**
     * Upload a post image
     */
    public function uploadPostImage(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Get the file first to check if it exists
            $imageFile = $request->file('image');
            
            // Log for debugging
            \Log::info('Image upload attempt', [
                'has_file' => $request->hasFile('image'),
                'all_files' => array_keys($request->allFiles()),
                'content_type' => $request->header('Content-Type'),
                'has_input' => $request->has('image'),
            ]);
            
            if (!$imageFile) {
                // Check if it's in the request but not as a file
                if ($request->has('image')) {
                    \Log::warning('Image sent as data, not file', [
                        'image_type' => gettype($request->input('image')),
                    ]);
                }
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Image file is required',
                    'errors' => ['image' => ['The image field is required.']]
                ], 422);
            }
            
            \Log::info('Image file received', [
                'original_name' => $imageFile->getClientOriginalName(),
                'extension' => $imageFile->getClientOriginalExtension(),
                'mime_type' => $imageFile->getMimeType(),
                'size' => $imageFile->getSize(),
            ]);

            // Validate file extension as fallback (for React Native compatibility)
            $extension = strtolower($imageFile->getClientOriginalExtension());
            $originalName = $imageFile->getClientOriginalName();
            
            // If extension is empty, try to get it from filename
            if (empty($extension) && !empty($originalName)) {
                $parts = explode('.', $originalName);
                if (count($parts) > 1) {
                    $extension = strtolower(end($parts));
                }
            }
            
            $allowedExtensions = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
            
            if (!empty($extension) && !in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid image format',
                    'errors' => ['image' => ['The image must be a file of type: jpeg, jpg, png, gif, webp.']]
                ], 422);
            }
            
            // If we still don't have an extension, check MIME type as last resort
            if (empty($extension)) {
                $mimeType = $imageFile->getMimeType();
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid image format',
                        'errors' => ['image' => ['The image must be a file of type: jpeg, jpg, png, gif, webp.']]
                    ], 422);
                }
            }

            // Validate file size
            if ($imageFile->getSize() > 5 * 1024 * 1024) { // 5MB
                return response()->json([
                    'status' => 'error',
                    'message' => 'Image too large',
                    'errors' => ['image' => ['The image may not be greater than 5MB.']]
                ], 422);
            }

            $imageUrl = $this->mediaService->uploadPostImage($imageFile, $user->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Image uploaded successfully',
                'data' => [
                    'url' => $imageUrl,
                    'size' => $imageFile->getSize(),
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Image upload error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
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

            $user = auth()->user();
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

            $user = auth()->user();
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

            $user = auth()->user();
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
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Get the file first to check if it exists
            $videoFile = $request->file('video');
            
            // Log for debugging
            \Log::info('Video upload attempt', [
                'has_file' => $request->hasFile('video'),
                'all_files' => array_keys($request->allFiles()),
                'content_type' => $request->header('Content-Type'),
            ]);
            
            if (!$videoFile) {
                // Check if it's in the request but not as a file
                if ($request->has('video')) {
                    \Log::warning('Video sent as data, not file', [
                        'video_type' => gettype($request->input('video')),
                    ]);
                }
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Video file is required',
                    'errors' => ['video' => ['The video field is required.']]
                ], 422);
            }
            
            \Log::info('Video file received', [
                'original_name' => $videoFile->getClientOriginalName(),
                'extension' => $videoFile->getClientOriginalExtension(),
                'mime_type' => $videoFile->getMimeType(),
                'size' => $videoFile->getSize(),
            ]);

            // Validate file extension as fallback (for React Native compatibility)
            $extension = strtolower($videoFile->getClientOriginalExtension());
            $originalName = $videoFile->getClientOriginalName();
            
            // If extension is empty, try to get it from filename
            if (empty($extension) && !empty($originalName)) {
                $parts = explode('.', $originalName);
                if (count($parts) > 1) {
                    $extension = strtolower(end($parts));
                }
            }
            
            $allowedExtensions = ['mp4', 'webm', 'mov', 'avi', 'mkv'];
            
            if (!empty($extension) && !in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid video format',
                    'errors' => ['video' => ['The video must be a file of type: mp4, webm, mov, avi, mkv.']]
                ], 422);
            }
            
            // If we still don't have an extension, check MIME type as last resort
            if (empty($extension)) {
                $mimeType = $videoFile->getMimeType();
                $allowedMimeTypes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'];
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid video format',
                        'errors' => ['video' => ['The video must be a file of type: mp4, webm, mov, avi, mkv.']]
                    ], 422);
                }
            }

            // Validate file size
            if ($videoFile->getSize() > 100 * 1024 * 1024) { // 100MB
                return response()->json([
                    'status' => 'error',
                    'message' => 'Video too large',
                    'errors' => ['video' => ['The video may not be greater than 100MB.']]
                ], 422);
            }
            
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
            
            // Generate thumbnail for the video
            $thumbnailUrl = $this->thumbnailService->generateThumbnail($videoUrl, $user->id);

            \Log::info('Video upload completed', [
                'user_id' => $user->id,
                'video_url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'file_size' => $videoFile->getSize(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Video uploaded successfully',
                'data' => [
                    'url' => $videoUrl,
                    'thumbnail' => $thumbnailUrl,
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

            \Log::error('Video upload error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            
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

            $user = auth()->user();
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

            $user = auth()->user();
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
