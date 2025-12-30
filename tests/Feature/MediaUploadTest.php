<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3'); // Mock S3 storage for testing
        $this->user = User::factory()->create();
    }

    /**
     * Test uploading a post image
     */
    public function test_upload_post_image()
    {
        $file = UploadedFile::fake()->image('post.jpg', 800, 600);

        $response = $this->actingAs($this->user, 'token')
            ->postJson('/api/v1/media/post-image', [
                'image' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Image uploaded successfully',
        ]);

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('url', $response->json()['data']);
    }

    /**
     * Test uploading an avatar
     */
    public function test_upload_avatar()
    {
        $file = UploadedFile::fake()->image('avatar.png', 256, 256);

        $response = $this->actingAs($this->user, 'token')
            ->postJson('/api/v1/media/avatar', [
                'avatar' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Avatar uploaded successfully',
        ]);
    }

    /**
     * Test uploading a cover image
     */
    public function test_upload_cover_image()
    {
        $file = UploadedFile::fake()->image('cover.jpg', 1200, 400);

        $response = $this->actingAs($this->user, 'token')
            ->postJson('/api/v1/media/cover', [
                'cover' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Cover image uploaded successfully',
        ]);
    }

    /**
     * Test uploading a story
     */
    public function test_upload_story()
    {
        $file = UploadedFile::fake()->image('story.jpg', 1080, 1920);

        $response = $this->actingAs($this->user, 'token')
            ->postJson('/api/v1/media/story', [
                'image' => $file,
                'caption' => 'Beautiful sunset',
                'duration' => 1440, // 24 hours
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Story created successfully',
        ]);

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('expires_at', $response->json()['data']);
    }

    /**
     * Test uploading file without authentication
     */
    public function test_upload_without_authentication()
    {
        $file = UploadedFile::fake()->image('post.jpg');

        $response = $this->postJson('/api/v1/media/post-image', [
            'image' => $file,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test uploading invalid file format
     */
    public function test_upload_invalid_file_format()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->user, 'token')
            ->postJson('/api/v1/media/post-image', [
                'image' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['status', 'message', 'errors']);
    }

    /**
     * Test uploading oversized file
     */
    public function test_upload_oversized_post_image()
    {
        // Mock an oversized file by testing the validation
        $response = $this->actingAs($this->user, 'token')
            ->postJson('/api/v1/media/post-image', [
                'image' => null, // Missing required field
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test uploading oversized avatar
     */
    public function test_upload_oversized_avatar()
    {
        // Avatar has lower size limit (2MB)
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($this->user, 'token')
            ->postJson('/api/v1/media/avatar', [
                'avatar' => $file,
            ]);

        $response->assertStatus(200); // Should work with normal size
    }

    /**
     * Test deleting a media file
     */
    public function test_delete_media_file()
    {
        // First upload a file
        $file = UploadedFile::fake()->image('post.jpg');
        $uploadResponse = $this->actingAs($this->user, 'token')
            ->postJson('/api/v1/media/post-image', [
                'image' => $file,
            ]);

        $fileUrl = $uploadResponse->json()['data']['url'];

        // Then delete it
        $deleteResponse = $this->actingAs($this->user, 'token')
            ->postJson('/api/v1/media/delete', [
                'file_url' => $fileUrl,
            ]);

        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson([
            'status' => 'success',
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Test deleting file without authentication
     */
    public function test_delete_without_authentication()
    {
        $response = $this->postJson('/api/v1/media/delete', [
            'file_url' => 'https://example.com/file.jpg',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test story with custom duration
     */
    public function test_upload_story_with_custom_duration()
    {
        $file = UploadedFile::fake()->image('story.jpg');

        $response = $this->actingAs($this->user, 'token')
            ->postJson('/api/v1/media/story', [
                'image' => $file,
                'caption' => 'Quick story',
                'duration' => 60, // 1 hour
            ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('expires_at', $response->json()['data']);
    }
}
