<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorageService
{
    const POSTS_PATH = 'posts';
    const STORIES_PATH = 'stories';
    const VIDEOS_PATH = 'videos';
    const VIDEO_STORIES_PATH = 'video-stories';
    const AVATARS_PATH = 'avatars';
    const COVERS_PATH = 'covers';

    // Supported file types
    const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'avi', 'mkv'];

    // File size limits (in bytes)
    const AVATAR_MAX_SIZE = 2 * 1024 * 1024; // 2 MB
    const COVER_MAX_SIZE = 5 * 1024 * 1024; // 5 MB
    const IMAGE_MAX_SIZE = 5 * 1024 * 1024; // 5 MB
    const VIDEO_MAX_SIZE = 100 * 1024 * 1024; // 100 MB

    /**
     * Upload a post image
     */
    public function uploadPostImage(UploadedFile $file, int $userId): string
    {
        return $this->uploadFile($file, self::POSTS_PATH . '/' . $userId);
    }

    /**
     * Upload a story image
     */
    public function uploadStoryImage(UploadedFile $file, int $userId): string
    {
        return $this->uploadFile($file, self::STORIES_PATH . '/' . $userId);
    }

    /**
     * Upload a user avatar
     */
    public function uploadAvatar(UploadedFile $file, int $userId): string
    {
        // Delete old avatar if exists
        $this->deleteAvatar($userId);

        return $this->uploadFile($file, self::AVATARS_PATH . '/' . $userId);
    }

    /**
     * Upload a cover image
     */
    public function uploadCoverImage(UploadedFile $file, int $userId): string
    {
        // Delete old cover if exists
        $this->deleteCoverImage($userId);

        return $this->uploadFile($file, self::COVERS_PATH . '/' . $userId);
    }

    /**
     * Upload a post video
     */
    public function uploadPostVideo(UploadedFile $file, int $userId): string
    {
        return $this->uploadFile($file, self::VIDEOS_PATH . '/' . $userId);
    }

    /**
     * Upload a story video
     */
    public function uploadStoryVideo(UploadedFile $file, int $userId): string
    {
        return $this->uploadFile($file, self::VIDEO_STORIES_PATH . '/' . $userId);
    }

    /**
     * Upload a file to S3
     */
    protected function uploadFile(UploadedFile $file, string $path): string
    {
        $filename = Str::random(32) . '.' . $file->getClientOriginalExtension();
        $fullPath = $path . '/' . $filename;

        // Store on S3/Wasabi
        Storage::disk('s3')->putFileAs($path, $file, $filename, 'public');

        // Return the URL
        return Storage::disk('s3')->url($fullPath);
    }

    /**
     * Delete a file from S3
     */
    public function deleteFile(string $url): bool
    {
        // Extract path from full URL
        $basePath = config('filesystems.disks.s3.endpoint') . '/' . config('filesystems.disks.s3.bucket') . '/';
        $path = str_replace($basePath, '', $url);

        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
            return true;
        }

        return false;
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar(int $userId): bool
    {
        return $this->deleteUserFiles(self::AVATARS_PATH . '/' . $userId);
    }

    /**
     * Delete cover image
     */
    public function deleteCoverImage(int $userId): bool
    {
        return $this->deleteUserFiles(self::COVERS_PATH . '/' . $userId);
    }

    /**
     * Delete user post
     */
    public function deletePostImage(int $userId, string $filename): bool
    {
        $path = self::POSTS_PATH . '/' . $userId . '/' . $filename;
        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
            return true;
        }
        return false;
    }

    /**
     * Delete story image
     */
    public function deleteStoryImage(int $userId, string $filename): bool
    {
        $path = self::STORIES_PATH . '/' . $userId . '/' . $filename;
        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
            return true;
        }
        return false;
    }

    /**
     * Delete all files in a user directory
     */
    protected function deleteUserFiles(string $directory): bool
    {
        if (Storage::disk('s3')->exists($directory)) {
            Storage::disk('s3')->deleteDirectory($directory);
            return true;
        }
        return false;
    }

    /**
     * Get all files in a directory
     */
    public function listFiles(string $path): array
    {
        return Storage::disk('s3')->listContents($path)->toArray();
    }

    /**
     * Get file size
     */
    public function getFileSize(string $path): int
    {
        return Storage::disk('s3')->size($path);
    }

    /**
     * Get file URL
     */
    public function getFileUrl(string $path): string
    {
        return Storage::disk('s3')->url($path);
    }

    /**
     * Check if file exists
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk('s3')->exists($path);
    }

    /**
     * Check if file is image
     */
    public function isImage(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, self::IMAGE_EXTENSIONS);
    }

    /**
     * Check if file is video
     */
    public function isVideo(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, self::VIDEO_EXTENSIONS);
    }

    /**
     * Validate file size for images
     */
    public function validateImageSize(UploadedFile $file): bool
    {
        return $file->getSize() <= self::IMAGE_MAX_SIZE;
    }

    /**
     * Validate file size for videos
     */
    public function validateVideoSize(UploadedFile $file): bool
    {
        return $file->getSize() <= self::VIDEO_MAX_SIZE;
    }

    /**
     * Validate file size for avatars
     */
    public function validateAvatarSize(UploadedFile $file): bool
    {
        return $file->getSize() <= self::AVATAR_MAX_SIZE;
    }

    /**
     * Validate file size for covers
     */
    public function validateCoverSize(UploadedFile $file): bool
    {
        return $file->getSize() <= self::COVER_MAX_SIZE;
    }

    /**
     * Get supported image extensions
     */
    public function getSupportedImageExtensions(): array
    {
        return self::IMAGE_EXTENSIONS;
    }

    /**
     * Get supported video extensions
     */
    public function getSupportedVideoExtensions(): array
    {
        return self::VIDEO_EXTENSIONS;
    }
}
