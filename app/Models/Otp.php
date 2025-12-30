<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'otp',
        'attempts',
        'expires_at',
        'verified',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified' => 'boolean',
    ];

    public function isExpired()
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    public function isVerified()
    {
        return $this->verified;
    }

    public function hasExceededAttempts()
    {
        return $this->attempts >= 5;
    }

    public static function findByEmail($email)
    {
        return self::where('email', $email)->first();
    }

    public static function generateOtp($email, $expiryMinutes = 10)
    {
        $otp = random_int(100000, 999999);
        
        // Delete any existing OTP for this email
        self::where('email', $email)->delete();
        
        // Create new OTP
        return self::create([
            'email' => $email,
            'otp' => (string) $otp,
            'attempts' => 0,
            'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
            'verified' => false,
        ]);
    }
}
