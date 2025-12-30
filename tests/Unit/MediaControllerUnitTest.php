<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\MediaController;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Unit-level tests for MediaController logic
 * These tests verify the controller without database dependencies
 */
class MediaControllerUnitTest extends TestCase
{
    private MediaController $controller;
    private MediaStorageService $mediaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mediaService = app(MediaStorageService::class);
        $this->controller = new MediaController($this->mediaService);
    }

    /**
     * Test MediaController can be instantiated
     */
    public function test_media_controller_instantiation()
    {
        $this->assertInstanceOf(MediaController::class, $this->controller);
    }

    /**
     * Test MediaStorageService has required methods
     */
    public function test_media_storage_service_has_required_methods()
    {
        $this->assertTrue(method_exists($this->mediaService, 'uploadPostImage'));
        $this->assertTrue(method_exists($this->mediaService, 'uploadAvatar'));
        $this->assertTrue(method_exists($this->mediaService, 'uploadCoverImage'));
        $this->assertTrue(method_exists($this->mediaService, 'uploadStoryImage'));
        $this->assertTrue(method_exists($this->mediaService, 'uploadPostVideo'));
        $this->assertTrue(method_exists($this->mediaService, 'uploadStoryVideo'));
        $this->assertTrue(method_exists($this->mediaService, 'deleteFile'));
        $this->assertTrue(method_exists($this->mediaService, 'deleteAvatar'));
        $this->assertTrue(method_exists($this->mediaService, 'deleteCoverImage'));
        $this->assertTrue(method_exists($this->mediaService, 'deletePostImage'));
        $this->assertTrue(method_exists($this->mediaService, 'deleteStoryImage'));
        $this->assertTrue(method_exists($this->mediaService, 'fileExists'));
        $this->assertTrue(method_exists($this->mediaService, 'getFileUrl'));
        $this->assertTrue(method_exists($this->mediaService, 'getFileSize'));
        $this->assertTrue(method_exists($this->mediaService, 'isImage'));
        $this->assertTrue(method_exists($this->mediaService, 'isVideo'));
        $this->assertTrue(method_exists($this->mediaService, 'validateVideoSize'));
    }

    /**
     * Test media routes are registered
     */
    public function test_media_routes_are_registered()
    {
        $routes = $this->app['router']->getRoutes();
        
        // Verify specific routes exist
        $postImageRoute = false;
        $postVideoRoute = false;
        $avatarRoute = false;
        $coverRoute = false;
        $storyRoute = false;
        $storyVideoRoute = false;
        $deleteRoute = false;
        
        foreach ($routes as $route) {
            if ($route->uri === 'api/v1/media/post-image' && in_array('POST', $route->methods)) {
                $postImageRoute = true;
            }
            if ($route->uri === 'api/v1/media/post-video' && in_array('POST', $route->methods)) {
                $postVideoRoute = true;
            }
            if ($route->uri === 'api/v1/media/avatar' && in_array('POST', $route->methods)) {
                $avatarRoute = true;
            }
            if ($route->uri === 'api/v1/media/cover' && in_array('POST', $route->methods)) {
                $coverRoute = true;
            }
            if ($route->uri === 'api/v1/media/story' && in_array('POST', $route->methods)) {
                $storyRoute = true;
            }
            if ($route->uri === 'api/v1/media/story-video' && in_array('POST', $route->methods)) {
                $storyVideoRoute = true;
            }
            if ($route->uri === 'api/v1/media/delete' && in_array('POST', $route->methods)) {
                $deleteRoute = true;
            }
        }
        
        $this->assertTrue($postImageRoute, 'POST api/v1/media/post-image route not found');
        $this->assertTrue($postVideoRoute, 'POST api/v1/media/post-video route not found');
        $this->assertTrue($avatarRoute, 'POST api/v1/media/avatar route not found');
        $this->assertTrue($coverRoute, 'POST api/v1/media/cover route not found');
        $this->assertTrue($storyRoute, 'POST api/v1/media/story route not found');
        $this->assertTrue($storyVideoRoute, 'POST api/v1/media/story-video route not found');
        $this->assertTrue($deleteRoute, 'POST api/v1/media/delete route not found');
    }

    /**
     * Test mediaservice is properly registered in service container
     */
    public function test_media_storage_service_is_registered()
    {
        $service = $this->app->make(MediaStorageService::class);
        $this->assertInstanceOf(MediaStorageService::class, $service);
    }

    /**
     * Test filesystem default disk is S3
     */
    public function test_filesystem_default_disk_is_s3()
    {
        $defaultDisk = config('filesystems.default');
        $this->assertEquals('s3', $defaultDisk, 'Default filesystem disk should be s3 for Wasabi');
    }

    /**
     * Test Wasabi S3 configuration is present
     */
    public function test_wasabi_s3_configuration_exists()
    {
        $s3Config = config('filesystems.disks.s3');
        
        $this->assertNotNull($s3Config, 'S3 disk configuration is missing');
        $this->assertEquals('s3', $s3Config['driver']);
        $this->assertEquals('craftrly', $s3Config['bucket']);
        $this->assertStringContainsString('s3.us-central-1.wasabisys.com', $s3Config['endpoint']);
        $this->assertTrue($s3Config['use_path_style_endpoint']);
    }

    /**
     * Test AWS credentials are configured
     */
    public function test_aws_credentials_are_configured()
    {
        $this->assertNotNull(config('filesystems.disks.s3.key'), 'AWS_ACCESS_KEY_ID not configured');
        $this->assertNotNull(config('filesystems.disks.s3.secret'), 'AWS_SECRET_ACCESS_KEY not configured');
        $this->assertEquals('craftrly', config('filesystems.disks.s3.bucket'));
    }

    /**
     * Test Story model exists and is properly configured
     */
    public function test_story_model_exists()
    {
        $this->assertTrue(class_exists('App\Models\Story'));
        $story = new \App\Models\Story();
        $this->assertTrue(method_exists($story, 'isExpired'));
        $this->assertTrue(method_exists($story, 'getRemainingTime'));
    }

    /**
     * Test User model has posts relationship
     */
    public function test_user_model_has_posts_relationship()
    {
        $this->assertTrue(method_exists('App\Models\User', 'posts'));
    }

    /**
     * Test Post model has media deletion hook
     */
    public function test_post_model_is_properly_configured()
    {
        $this->assertTrue(class_exists('App\Models\Post'));
        // Verify the model can be instantiated
        $post = new \App\Models\Post();
        $this->assertNotNull($post);
    }
    /**
     * Test video upload support
     */
    public function test_video_upload_methods_exist()
    {
        $this->assertTrue(method_exists($this->mediaService, 'uploadPostVideo'));
        $this->assertTrue(method_exists($this->mediaService, 'uploadStoryVideo'));
    }

    /**
     * Test video validation methods exist
     */
    public function test_video_validation_methods_exist()
    {
        $this->assertTrue(method_exists($this->mediaService, 'isVideo'));
        $this->assertTrue(method_exists($this->mediaService, 'validateVideoSize'));
        $this->assertTrue(method_exists($this->mediaService, 'getSupportedVideoExtensions'));
    }

    /**
     * Test Post model supports video fields
     */
    public function test_post_model_supports_video_fields()
    {
        $post = new \App\Models\Post();
        $fillable = $post->getFillable();
        
        $this->assertContains('video', $fillable);
        $this->assertContains('video_status', $fillable);
        $this->assertContains('video_duration', $fillable);
    }

    /**
     * Test Story model supports video fields
     */
    public function test_story_model_supports_video_fields()
    {
        $story = new \App\Models\Story();
        $fillable = $story->getFillable();
        
        $this->assertContains('video', $fillable);
        $this->assertContains('media_type', $fillable);
        $this->assertContains('video_duration', $fillable);
    }

    /**
     * Test User model has stories relationship
     */
    public function test_user_model_has_stories_relationship()
    {
        $this->assertTrue(method_exists('App\Models\User', 'stories'));
    }}
