<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoThumbnailService
{
    protected MediaStorageService $mediaService;

    public function __construct(MediaStorageService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Generate a thumbnail URL for a video
     * For now, returns a placeholder or uses video as fallback
     * In production, you would use ffmpeg or a video processing service
     */
    public function generateThumbnail(string $videoUrl, int $userId): ?string
    {
        try {
            // For MVP, return a placeholder thumbnail URL
            // In production, you would:
            // 1. Download the video
            // 2. Extract the first frame using ffmpeg
            // 3. Store as an image
            // 4. Return the thumbnail URL
            
            // Fallback: Create a simple placeholder
            $thumbnailUrl = $this->createPlaceholderThumbnail($userId);
            
            return $thumbnailUrl;
        } catch (\Exception $e) {
            \Log::error('Thumbnail generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a simple placeholder thumbnail
     * In production, this would extract actual frame from video
     */
    private function createPlaceholderThumbnail(int $userId): ?string
    {
        try {
            // For now, generate a simple colored SVG placeholder
            $colors = ['FF6B6B', '4ECDC4', '45B7D1', 'F7B731', '5F27CD'];
            $color = $colors[array_rand($colors)];
            
            $svgContent = <<<SVG
<svg width="320" height="568" xmlns="http://www.w3.org/2000/svg">
    <rect width="320" height="568" fill="#{$color}"/>
    <text x="160" y="284" font-size="24" fill="white" text-anchor="middle" dominant-baseline="middle">
        Video
    </text>
</svg>
SVG;

            $filename = 'thumb_' . Str::random(32) . '.svg';
            $path = 'thumbnails/' . $userId;
            
            Storage::disk('s3')->put(
                $path . '/' . $filename,
                $svgContent,
                'private'
            );

            $fullPath = $path . '/' . $filename;
            
            // Return presigned URL for thumbnail
            return $this->generatePresignedUrl($fullPath);
        } catch (\Exception $e) {
            \Log::error('Placeholder thumbnail creation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a presigned URL for S3/Wasabi files
     */
    private function generatePresignedUrl(string $path): string
    {
        try {
            $disk = Storage::disk('s3');
            $adapter = $disk->getAdapter();
            
            // Use reflection to access the private client property
            $reflection = new \ReflectionClass($adapter);
            $clientProperty = $reflection->getProperty('client');
            $clientProperty->setAccessible(true);
            $client = $clientProperty->getValue($adapter);
            
            $bucketProperty = $reflection->getProperty('bucket');
            $bucketProperty->setAccessible(true);
            $bucket = $bucketProperty->getValue($adapter);
            
            $cmd = $client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $path,
            ]);
            
            $request = $client->createPresignedRequest($cmd, '+7 days');
            return (string)$request->getUri();
        } catch (\Exception $e) {
            \Log::warning('Failed to generate presigned URL for thumbnail: ' . $e->getMessage());
            return Storage::disk('s3')->url($path);
        }
    }
}
