<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Services\MediaStorageService;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'verified',
        'verified_type',
        'phone',
        'bio',
        'is_active',
        'is_banned',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_banned' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        // Delete avatar and cover when user is deleted
        static::deleting(function ($user) {
            try {
                if ($user->avatar) {
                    app(MediaStorageService::class)->deleteAvatar($user->id);
                }
                app(MediaStorageService::class)->deleteCoverImage($user->id);
            } catch (\Exception $e) {
                // Log the error but don't fail the deletion
                \Illuminate\Support\Facades\Log::error('Failed to delete user media: ' . $e->getMessage());
            }
        });
    }

    public function tokens()
    {
        return $this->hasMany(\App\Models\ApiToken::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function followers()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'followed_id',
            'follower_id'
        );
    }

    public function following()
    {
        return $this->belongsToMany(
            User::class,
            'follows',
            'follower_id',
            'followed_id'
        );
    }

    public function isFollowedBy($user)
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    public function isFollowing($user)
    {
        return $this->following()->where('followed_id', $user->id)->exists();
    }
}
