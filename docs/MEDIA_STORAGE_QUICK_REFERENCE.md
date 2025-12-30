# Media Storage - Quick Reference Guide

## API Endpoints

### Upload Post Image
```bash
POST /api/v1/media/post-image
Authorization: Bearer {token}
Content-Type: multipart/form-data

image: file (max 5MB, image only)

Response:
{
  "status": "success",
  "message": "Image uploaded successfully",
  "data": {
    "url": "https://s3.us-central-1.wasabisys.com/craftrly/posts/1/...",
    "size": 12345
  }
}
```

### Upload Avatar
```bash
POST /api/v1/media/avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data

avatar: file (max 2MB, image only)

Response:
{
  "status": "success",
  "message": "Avatar uploaded successfully",
  "data": {
    "url": "https://s3.us-central-1.wasabisys.com/craftrly/avatars/1/..."
  }
}
```

### Upload Cover Image
```bash
POST /api/v1/media/cover
Authorization: Bearer {token}
Content-Type: multipart/form-data

cover: file (max 5MB, image only)

Response:
{
  "status": "success",
  "message": "Cover image uploaded successfully",
  "data": {
    "url": "https://s3.us-central-1.wasabisys.com/craftrly/covers/1/..."
  }
}
```

### Upload Story
```bash
POST /api/v1/media/story
Authorization: Bearer {token}
Content-Type: multipart/form-data

image: file (max 5MB, image only)
caption: string (optional, max 500 chars)
duration: integer (optional, minutes, default 1440 = 24h)

Response:
{
  "status": "success",
  "message": "Story created successfully",
  "data": {
    "id": 1,
    "image": "https://s3.us-central-1.wasabisys.com/craftrly/stories/1/...",
    "caption": "Beautiful sunset",
    "expires_at": "2024-12-31T18:25:00Z",
    "is_deleted": false,
    "created_at": "2024-12-30T18:25:00Z"
  }
}
```

### Delete File
```bash
POST /api/v1/media/delete
Authorization: Bearer {token}
Content-Type: application/json

{
  "file_url": "https://s3.us-central-1.wasabisys.com/craftrly/posts/1/..."
}

Response:
{
  "status": "success",
  "message": "File deleted successfully",
  "data": {
    "deleted_file": "https://s3.us-central-1.wasabisys.com/craftrly/posts/1/..."
  }
}
```

---

## File Size Limits

| Type | Max Size |
|------|----------|
| Post Image | 5 MB |
| Avatar | 2 MB |
| Cover | 5 MB |
| Story | 5 MB |

---

## Supported Formats

- JPEG
- PNG
- GIF
- WebP

---

## Storage Organization

```
craftrly/
├── posts/{user_id}/
├── stories/{user_id}/
├── avatars/{user_id}/
└── covers/{user_id}/
```

---

## Story Expiration

- Default: 24 hours
- Configurable: 5 minutes to 30 days
- Auto-cleanup: Not automatic (mark as is_deleted)
- Query: Use `isExpired()` method on Story model

---

## Key Classes

### MediaStorageService
```php
// Inject in controller
use App\Services\MediaStorageService;

public function __construct(MediaStorageService $mediaService) {
    $this->mediaService = $mediaService;
}

// Upload methods
$url = $this->mediaService->uploadPostImage($file, $userId);
$url = $this->mediaService->uploadAvatar($file, $userId);
$url = $this->mediaService->uploadCoverImage($file, $userId);
$url = $this->mediaService->uploadStoryImage($file, $userId);

// Delete methods
$this->mediaService->deleteFile($url);
$this->mediaService->deleteAvatar($userId);

// Utility methods
$exists = $this->mediaService->fileExists($path);
$size = $this->mediaService->getFileSize($path);
$url = $this->mediaService->getFileUrl($path);
```

### Story Model
```php
use App\Models\Story;

// Check expiration
if ($story->isExpired()) {
    // Story has expired
}

// Get remaining time
$seconds = $story->getRemainingTime();

// Get user
$user = $story->user;
```

---

## Configuration

### .env
```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=S9BTYX7LM08QTNOP6ULL
AWS_SECRET_ACCESS_KEY=CDjrJAf2EU2HV7be9qilPhGimINI4XNThKvomk8U
AWS_DEFAULT_REGION=us-central-1
AWS_BUCKET=craftrly
AWS_ENDPOINT=https://s3.us-central-1.wasabisys.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### Filesystems Config
```php
'default' => 's3', // Default disk is S3

's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT'),
],
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "status": "error",
  "message": "Unauthorized"
}
```

### 403 Forbidden
```json
{
  "status": "error",
  "message": "Forbidden - you can only delete your own files"
}
```

### 422 Validation Error
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "image": ["The image must be an image"]
  }
}
```

### 500 Server Error
```json
{
  "status": "error",
  "message": "Server error - could not upload file"
}
```

---

## Testing

### Run Unit Tests
```bash
php artisan test tests/Unit/MediaControllerUnitTest.php
```

### Run Feature Tests (requires database)
```bash
php artisan test tests/Feature/MediaUploadTest.php
```

### Quick Verification
```bash
php artisan route:list --path=media
php artisan tinker
> config('filesystems.default')
> config('filesystems.disks.s3.bucket')
```

---

## Common Tasks

### Upload user avatar
```php
$url = $mediaService->uploadAvatar($request->file('avatar'), auth()->id());
// Old avatar automatically deleted
```

### Upload post with image
```php
$imageUrl = $mediaService->uploadPostImage($request->file('image'), auth()->id());
$post = Post::create([
    'user_id' => auth()->id(),
    'image' => $imageUrl,
    'caption' => $request->caption,
]);
```

### Create story
```php
$imageUrl = $mediaService->uploadStoryImage($request->file('image'), auth()->id());
$story = Story::create([
    'user_id' => auth()->id(),
    'image' => $imageUrl,
    'caption' => $request->caption,
    'expires_at' => now()->addHours(24),
]);
```

### Delete user and all media
```php
$user = User::find(1);
// Avatar and cover automatically deleted from S3
$user->delete();
```

### Check expired stories
```php
$expiredStories = Story::whereNotNull('expires_at')
    ->where('expires_at', '<', now())
    ->get();

foreach ($expiredStories as $story) {
    $story->update(['is_deleted' => true]);
}
```

---

## Database Schema

### Stories Table
```sql
CREATE TABLE stories (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL FOREIGN KEY (REFERENCES users),
  image VARCHAR(255) NOT NULL,
  caption TEXT,
  expires_at TIMESTAMP,
  is_deleted TINYINT DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_expires_at (expires_at)
);
```

---

## Routes Protected By

All media routes protected with: `auth:token` middleware

Example middleware check in controller:
```php
$user = auth('token')->user();
if (!$user) {
    return response()->json(['error' => 'Unauthorized'], 401);
}
```

---

## File Cleanup Policies

### Automatic Deletion
- ✅ When user is deleted → avatar, cover deleted from S3
- ✅ When post is deleted → post image deleted from S3
- ✅ When story is deleted → story image deleted from S3

### Manual Deletion
- Via /api/v1/media/delete endpoint (user must own file)

### Automatic Replacement
- Avatar: New upload deletes old avatar
- Cover: New upload deletes old cover

---

## Performance Tips

1. **Use CDN**: Put S3 URLs behind CloudFront or similar CDN
2. **Compress Images**: Consider image compression before upload
3. **Use Thumbnails**: Generate thumbnails for faster loading
4. **Lazy Load**: Load story images as user scrolls
5. **Cache URLs**: Cache S3 URLs in Redis if frequently accessed

---

## Troubleshooting

### File not found after upload
- Verify FILESYSTEM_DISK=s3 in .env
- Check AWS credentials are correct
- Verify bucket name matches

### Upload fails with 422
- Check file format (must be image)
- Check file size (under limit)
- Verify multipart/form-data content type

### Files not deleted
- Verify user ownership (correct user_id in path)
- Check AWS credentials have delete permission
- Verify S3 bucket allows deletions

### Story not expiring
- Use isExpired() method to check
- Manually mark is_deleted=1
- Implement cleanup job if auto-cleanup needed

---

**Last Updated**: December 30, 2024
**Version**: 1.0
**Status**: Production Ready ✅

