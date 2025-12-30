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
        'image',
        'video',
        'video_status',
        'video_duration',
        'is_flagged',
        'flag_reason',
        'flagged_at',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
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
     * Check if the post is flagged.
     */
    public function isFlagged(): bool
    {
        return $this->is_flagged ?? false;
    }

    /**
     * Flag the post.
     */
    public function flag(string $reason = null)
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
