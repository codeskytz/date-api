# Video Support Implementation

## Overview
Complete video upload and storage support for both posts and stories, extending the existing media infrastructure with video-specific handling.

## New Features

### 1. Post Videos
- Upload video to post (max 100MB)
- Track video processing status (pending, processing, ready, failed)
- Store video duration metadata
- Automatic deletion when post is deleted

### 2. Story Videos
- Upload video to story (max 100MB)
- Automatic 24-hour expiration (configurable 5 min - 30 days)
- Track media type (image or video)
- Store video duration metadata
- Automatic deletion when story is deleted

## File Size and Format Specifications

### Supported Video Formats
- MP4 (H.264 codec recommended)
- WebM (VP8/VP9 codec)
- MOV (QuickTime)
- AVI
- MKV

### Size Limits
```
Post Videos:     100 MB max
Story Videos:    100 MB max
Post Images:     5 MB max (unchanged)
Story Images:    5 MB max (unchanged)
Avatar:          2 MB max (unchanged)
Cover:           5 MB max (unchanged)
```

## API Endpoints

### Upload Post Video
```
POST /api/v1/media/post-video
Authorization: Bearer {token}
Content-Type: multipart/form-data

video: file (max 100MB)

Response:
{
  "status": "success",
  "message": "Video uploaded successfully",
  "data": {
    "url": "https://s3.us-central-1.wasabisys.com/craftrly/videos/{user_id}/...",
    "size": 52428800,
    "status": "pending"
  }
}
```

### Upload Story Video
```
POST /api/v1/media/story-video
Authorization: Bearer {token}
Content-Type: multipart/form-data

video: file (max 100MB)
caption: string (optional, max 500 chars)
duration: integer (optional, minutes, default 1440)

Response:
{
  "status": "success",
  "message": "Video story created successfully",
  "data": {
    "id": 1,
    "url": "https://s3.us-central-1.wasabisys.com/craftrly/video-stories/{user_id}/...",
    "media_type": "video",
    "expires_at": "2024-12-31T18:25:00Z",
    "size": 52428800
  }
}
```

## Database Schema Changes

### Posts Table
```sql
ALTER TABLE posts ADD COLUMN image VARCHAR(255);
ALTER TABLE posts ADD COLUMN video VARCHAR(255);
ALTER TABLE posts ADD COLUMN video_status VARCHAR(50) DEFAULT 'pending';
ALTER TABLE posts ADD COLUMN video_duration INT;
```

Fields:
- `image`: Image file URL on S3
- `video`: Video file URL on S3
- `video_status`: Status of video (pending, processing, ready, failed)
- `video_duration`: Video duration in seconds

### Stories Table
```sql
ALTER TABLE stories ADD COLUMN video VARCHAR(255);
ALTER TABLE stories ADD COLUMN media_type VARCHAR(50) DEFAULT 'image';
ALTER TABLE stories ADD COLUMN video_duration INT;
```

Fields:
- `video`: Video file URL on S3
- `media_type`: Type of media (image or video)
- `video_duration`: Video duration in seconds

## Storage Organization

```
craftrly/ (S3 Bucket)
├── posts/{user_id}/           # Post images (image column)
├── videos/{user_id}/          # Post videos (video column)
├── stories/{user_id}/         # Story images (image column)
├── video-stories/{user_id}/   # Story videos (video column)
├── avatars/{user_id}/         # Avatar images (unchanged)
└── covers/{user_id}/          # Cover images (unchanged)
```

## Service Layer Updates

### MediaStorageService

**New Methods for Videos:**
```php
public function uploadPostVideo(UploadedFile $file, int $userId): string
public function uploadStoryVideo(UploadedFile $file, int $userId): string
```

**New Validation Methods:**
```php
public function isImage(UploadedFile $file): bool
public function isVideo(UploadedFile $file): bool
public function validateImageSize(UploadedFile $file): bool
public function validateVideoSize(UploadedFile $file): bool
public function validateAvatarSize(UploadedFile $file): bool
public function validateCoverSize(UploadedFile $file): bool
public function getSupportedImageExtensions(): array
public function getSupportedVideoExtensions(): array
```

**Constants Added:**
```php
const VIDEOS_PATH = 'videos';
const VIDEO_STORIES_PATH = 'video-stories';

// Supported file types
const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'avi', 'mkv'];

// File size limits
const VIDEO_MAX_SIZE = 100 * 1024 * 1024; // 100 MB
```

## Model Updates

### Post Model
- Added `image`, `video`, `video_status`, `video_duration` to fillable
- Updated deletion hook to delete both image and video
- Backward compatible with existing posts

### Story Model
- Added `video`, `media_type`, `video_duration` to fillable
- Updated deletion hook to delete both image and video
- Media type defaults to 'image', can be set to 'video'

### User Model
- Added `stories()` relationship: `hasMany(Story::class)`
- Supports story creation: `$user->stories()->create([...])`

## Controller Updates

### MediaController

**New Methods:**
```php
public function uploadPostVideo(Request $request)
// Validates: mimes:mp4,webm,mov,avi,mkv, max:102400
// Returns: url, size, status

public function uploadStoryVideo(Request $request)
// Validates: mimes:mp4,webm,mov,avi,mkv, max:102400
// Creates story with video, caption, expiration
// Returns: id, url, media_type, expires_at, size
```

## Route Changes

### New Routes
```
POST /api/v1/media/post-video      → uploadPostVideo
POST /api/v1/media/story-video     → uploadStoryVideo
```

### Existing Routes (Unchanged)
```
POST /api/v1/media/post-image      → uploadPostImage
POST /api/v1/media/avatar          → uploadAvatar
POST /api/v1/media/cover           → uploadCoverImage
POST /api/v1/media/story           → uploadStory (image)
POST /api/v1/media/delete          → deleteFile
```

## Migration Details

**Migration 2025_12_30_000004_add_video_support_to_posts.php**
- Status: ✅ Executed (158.70ms)
- Adds: image, video, video_status, video_duration columns

**Migration 2025_12_30_000005_add_video_support_to_stories.php**
- Status: ✅ Executed (120.36ms)
- Adds: video, media_type, video_duration columns

## Usage Examples

### Upload Post Video
```php
$file = $request->file('video');
$url = app(MediaStorageService::class)->uploadPostVideo($file, auth()->id());

// Store in database
Post::create([
    'user_id' => auth()->id(),
    'video' => $url,
    'video_status' => 'pending',
    'description' => $request->description,
]);
```

### Upload Story Video
```php
// API automatically handles story creation
$response = $this->postJson('/api/v1/media/story-video', [
    'video' => $file,
    'caption' => 'Check out this video!',
    'duration' => 1440, // 24 hours
]);

// Story is created with:
// - video URL
// - media_type = 'video'
// - expires_at = now()->addMinutes(1440)
```

### Query Story by Media Type
```php
// Get all image stories
$imageStories = Story::where('media_type', 'image')->get();

// Get all video stories
$videoStories = Story::where('media_type', 'video')->get();

// Get all stories (mixed)
$allStories = Story::where('is_deleted', false)
    ->where('expires_at', '>', now())
    ->get();
```

### Check Video Duration
```php
$story = Story::find(1);
if ($story->media_type === 'video') {
    echo "Duration: {$story->video_duration} seconds";
}
```

## Video Processing Workflow

### Post Video Status
- `pending`: Video uploaded, waiting for processing
- `processing`: Video being processed/transcoded
- `ready`: Video ready to stream
- `failed`: Processing failed

### Recommended Implementation
```php
// Mark video as processing
$post->update(['video_status' => 'processing']);

// Queue job to process video
ProcessPostVideo::dispatch($post);

// Update status when complete
$post->update([
    'video_status' => 'ready',
    'video_duration' => $duration,
]);
```

## Security

### Authentication
✅ All video endpoints require `auth:token` middleware

### Authorization
✅ User ownership verified via URL path parsing

### File Validation
✅ MIME type validation (mimes:mp4,webm,mov,avi,mkv)
✅ File size validation (100MB max)
✅ File extension validation via service methods

### Credentials
✅ AWS credentials stored in .env
✅ Not hardcoded in code

## Performance Considerations

### File Size
- 100MB max is reasonable for typical video files
- Consider implementing progress tracking for large uploads
- May need timeout adjustment for slow connections

### Storage
- Organized by user_id for efficient access
- Separate directories for post and story videos
- Can be cached/served via CDN

### Transcoding
- Consider implementing video transcoding for multiple quality levels
- FFmpeg integration recommended for production
- Store different quality versions in separate directories

### Streaming
- HLS (HTTP Live Streaming) recommended for large videos
- Implement adaptive bitrate streaming
- Consider CloudFlare or similar CDN

## Optional Enhancements

1. **Video Processing Queue**
   - Transcode videos to multiple formats
   - Generate thumbnails
   - Extract metadata

2. **Streaming**
   - Implement HLS/DASH streaming
   - Progressive download support

3. **Metadata Extraction**
   - Auto-detect video duration
   - Extract video dimensions
   - Generate preview thumbnails

4. **Quality Variants**
   - Store 480p, 720p, 1080p versions
   - Adaptive bitrate streaming
   - Mobile-optimized variants

5. **Video Analytics**
   - Track video views
   - Watch time statistics
   - Engagement metrics

## Testing

### Unit Tests (Prepared)
Tests for video upload validation are prepared in `tests/Unit/MediaControllerUnitTest.php`

### Feature Tests (Prepared)
Tests for video upload endpoints are prepared in `tests/Feature/MediaUploadTest.php`

To run tests:
```bash
# Unit tests
php artisan test tests/Unit/MediaControllerUnitTest.php

# Feature tests (requires database)
php artisan test tests/Feature/MediaUploadTest.php
```

## Status

✅ **Video support fully implemented and deployed**

### Completed
- ✅ MediaStorageService extended with video methods
- ✅ Post model updated with video fields
- ✅ Story model updated with video support
- ✅ Two new API endpoints (post-video, story-video)
- ✅ Database migrations executed
- ✅ Complete documentation created
- ✅ Validation and error handling implemented

### Ready For
- ✅ Integration with post creation endpoints
- ✅ Integration with story feed
- ✅ Video processing queue implementation
- ✅ Streaming setup

### Optional (Future)
- ⚠️ Video transcoding service
- ⚠️ Thumbnail generation
- ⚠️ Quality variants (480p, 720p, 1080p)
- ⚠️ Video analytics tracking

## Configuration

No additional .env variables needed. Video uploads use existing S3 configuration:
```
FILESYSTEM_DISK=s3
AWS_BUCKET=craftrly
AWS_ENDPOINT=https://s3.us-central-1.wasabisys.com
```

## Migration Summary

| Migration | Status | Duration | Changes |
|-----------|--------|----------|---------|
| 2025_12_30_000004_add_video_support_to_posts | ✅ | 158.70ms | +4 columns |
| 2025_12_30_000005_add_video_support_to_stories | ✅ | 120.36ms | +3 columns |

**Total Migration Time**: 278.06ms

