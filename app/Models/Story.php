<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\MediaStorageService;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image',
        'video',
        'media_type',
        'caption',
        'video_duration',
        'expires_at',
        'is_deleted',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Delete image and video when story is deleted
        static::deleting(function ($story) {
            $service = app(MediaStorageService::class);
            
            if ($story->image) {
                $service->deleteFile($story->image);
            }
            
            if ($story->video) {
                $service->deleteFile($story->video);
            }
        });
    }

    /**
     * Get the user that owns the story.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if story is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get remaining time in seconds
     */
    public function getRemainingTime(): int
    {
        if (!$this->expires_at) {
            return 0;
        }

        $remaining = $this->expires_at->diffInSeconds(now(), false);
        return max(0, $remaining);
    }
}
