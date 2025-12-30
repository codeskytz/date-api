# Video Upload - Developer Quick Start

## Upload Post Video

```bash
curl -X POST http://localhost:8000/api/v1/media/post-video \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "video=@video.mp4"
```

**Response:**
```json
{
  "status": "success",
  "message": "Video uploaded successfully",
  "data": {
    "url": "https://s3.us-central-1.wasabisys.com/craftrly/videos/1/...",
    "size": 52428800,
    "status": "pending"
  }
}
```

**Then create post with video:**
```php
Post::create([
    'user_id' => auth()->id(),
    'video' => $response['data']['url'],
    'video_status' => 'pending',
    'description' => 'Check out this video!'
]);
```

---

## Upload Story Video

```bash
curl -X POST http://localhost:8000/api/v1/media/story-video \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "video=@video.mp4" \
  -F "caption=Amazing video!" \
  -F "duration=1440"
```

**Response:**
```json
{
  "status": "success",
  "message": "Video story created successfully",
  "data": {
    "id": 1,
    "url": "https://s3.us-central-1.wasabisys.com/craftrly/video-stories/1/...",
    "media_type": "video",
    "expires_at": "2024-12-31T18:25:00Z",
    "size": 52428800
  }
}
```

---

## Query Stories by Type

```php
// Get all video stories
$videos = Story::where('media_type', 'video')->get();

// Get all image stories
$images = Story::where('media_type', 'image')->get();

// Get active non-expired stories
$active = Story::where('expires_at', '>', now())
    ->where('is_deleted', false)
    ->get();

// Get user's video stories
$userVideos = auth()->user()->stories()
    ->where('media_type', 'video')
    ->get();
```

---

## Check Video Status

```php
$post = Post::find(1);

if ($post->video) {
    switch ($post->video_status) {
        case 'pending':
            echo "Processing...";
            break;
        case 'processing':
            echo "Video is being processed";
            break;
        case 'ready':
            echo "Ready to play";
            break;
        case 'failed':
            echo "Processing failed";
            break;
    }
}
```

---

## File Specifications

| Parameter | Limit |
|-----------|-------|
| Post Video Max Size | 100 MB |
| Story Video Max Size | 100 MB |
| Supported Formats | mp4, webm, mov, avi, mkv |
| Story Expiration | 24 hours (default) |
| Min Expiration | 5 minutes |
| Max Expiration | 30 days |
| Caption Length | 500 characters |

---

## Common Workflows

### Create Post with Video
```php
// 1. Upload video
$videoUrl = app(MediaStorageService::class)
    ->uploadPostVideo($request->file('video'), auth()->id());

// 2. Create post
$post = Post::create([
    'user_id' => auth()->id(),
    'video' => $videoUrl,
    'video_status' => 'pending',
    'description' => $request->description,
]);

// 3. (Optional) Queue processing job
ProcessPostVideo::dispatch($post);
```

### Create Story with Video
```php
// API handles this automatically:
$response = Http::post('api/v1/media/story-video', [
    'video' => $file,
    'caption' => 'Check this out!',
    'duration' => 1440,
]);

// Story is created and returned
$story = $response['data'];
```

### Update Video Status
```php
$post->update([
    'video_status' => 'ready',
    'video_duration' => 120, // seconds
]);
```

### Delete Post (Cascades)
```php
// Automatically deletes video from S3
$post->delete();
```

### Delete Story (Cascades)
```php
// Automatically deletes video from S3
$story->delete();
```

---

## Error Handling

```php
try {
    $response = $this->postJson('/api/v1/media/post-video', [
        'video' => $file
    ]);

    if ($response->status() === 200) {
        $videoUrl = $response['data']['url'];
    } elseif ($response->status() === 422) {
        // Validation error
        $errors = $response['errors'];
    } elseif ($response->status() === 401) {
        // Not authenticated
    } elseif ($response->status() === 500) {
        // Server error
    }
} catch (Exception $e) {
    // Handle error
}
```

---

## Tips & Best Practices

1. **Always set video_status to 'pending'** when creating posts with video
2. **Implement video processing queue** for transcoding
3. **Use MP4 format** for best compatibility
4. **Validate file size** on client before uploading
5. **Show upload progress** with progress events
6. **Cache video URLs** to reduce API calls
7. **Serve through CDN** for faster playback
8. **Generate thumbnails** from video frames

---

## Testing Videos

```php
// Unit test video validation
$this->assertTrue($service->isVideo($mp4File));
$this->assertTrue($service->validateVideoSize($mp4File));

// Feature test upload
$response = $this->actingAs($user, 'token')
    ->postJson('/api/v1/media/post-video', [
        'video' => UploadedFile::fake()->video('test.mp4')
    ]);

$response->assertStatus(200);
```

---

## Support

**Supported Formats:** mp4, webm, mov, avi, mkv  
**Max Size:** 100 MB  
**Storage:** Wasabi S3 (craftrly bucket)  
**Expiration:** Configurable (stories only)  
**Processing:** Pending status for future transcoding

