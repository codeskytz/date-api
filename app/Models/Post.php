<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\MediaStorageService;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'content',
        'media',
        'image',
        'video',
        'thumbnail',
        'video_status',
        'video_duration',
        'likes_count',
        'comments_count',
        'is_flagged',
        'is_reel',
        'flag_reason',
        'flagged_at',
    ];

    protected $casts = [
        'media' => 'array',
        'is_flagged' => 'boolean',
        'is_reel' => 'boolean',
        'flagged_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Delete image and video when post is deleted
        static::deleting(function ($post) {
            $service = app(MediaStorageService::class);
            
            if ($post->image) {
                $service->deleteFile($post->image);
            }
            
            if ($post->video) {
                $service->deleteFile($post->video);
            }
        });
    }

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the likes for the post.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the saved posts relationship.
     */
    public function savedBy()
    {
        return $this->hasMany(SavedPost::class);
    }

    /**
     * Check if the post is saved by a specific user.
     */
    public function isSavedBy($user)
    {
        if (!$user) {
            return false;
        }
        return $this->savedBy()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if the post is liked by a specific user.
     */
    public function isLikedBy($user)
    {
        if (!$user) {
            return false;
        }
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if the post is flagged.
     */
    public function isFlagged(): bool
    {
        return $this->is_flagged ?? false;
    }

    /**
     * Flag the post.
     */
    public function flag(?string $reason = null)
    {
        $this->update([
            'is_flagged' => true,
            'flag_reason' => $reason,
            'flagged_at' => now(),
        ]);

        return $this;
    }

    /**
     * Unflag the post.
     */
    public function unflag()
    {
        $this->update([
            'is_flagged' => false,
            'flag_reason' => null,
            'flagged_at' => null,
        ]);

        return $this;
    }
}
