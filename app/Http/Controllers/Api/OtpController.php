<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        // Check if email exists (optional - remove if you want to allow OTP for any email)
        // $user = User::where('email', $data['email'])->first();
        // if (!$user) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Email not found'
        //     ], 404);
        // }

        // Generate OTP
        $otp = Otp::generateOtp($data['email']);

        // Send OTP via email
        try {
            Mail::to($data['email'])->send(new SendOtpMail($data['email'], $otp->otp));
            
            return response()->json([
                'status' => 'success',
                'message' => 'OTP sent successfully to your email',
                'email' => $data['email'],
                'expires_in' => '10 minutes'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send OTP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        // Find OTP record
        $otpRecord = Otp::findByEmail($data['email']);

        if (!$otpRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP not found. Please request a new one.'
            ], 404);
        }

        // Check if OTP is already verified
        if ($otpRecord->isVerified()) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP already verified'
            ], 400);
        }

        // Check if OTP has expired
        if ($otpRecord->isExpired()) {
            $otpRecord->delete();
            return response()->json([
                'status' => 'error',
                'message' => 'OTP has expired. Please request a new one.'
            ], 410);
        }

        // Check if maximum attempts exceeded
        if ($otpRecord->hasExceededAttempts()) {
            $otpRecord->delete();
            return response()->json([
                'status' => 'error',
                'message' => 'Maximum verification attempts exceeded. Please request a new OTP.'
            ], 429);
        }

        // Verify OTP
        if ($otpRecord->otp !== $data['otp']) {
            $otpRecord->increment('attempts');
            
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP',
                'attempts_remaining' => 5 - $otpRecord->attempts
            ], 400);
        }

        // Mark as verified
        $otpRecord->update(['verified' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP verified successfully',
            'email' => $data['email'],
            'verified' => true
        ], 200);
    }

    public function resend(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        // Find existing OTP
        $otpRecord = Otp::findByEmail($data['email']);

        if ($otpRecord && !$otpRecord->isExpired()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please wait before requesting a new OTP'
            ], 429);
        }

        // Generate new OTP
        $otp = Otp::generateOtp($data['email']);

        // Send OTP via email
        try {
            Mail::to($data['email'])->send(new SendOtpMail($data['email'], $otp->otp));
            
            return response()->json([
                'status' => 'success',
                'message' => 'New OTP sent successfully',
                'email' => $data['email'],
                'expires_in' => '10 minutes'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to resend OTP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkStatus(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $otpRecord = Otp::findByEmail($data['email']);

        if (!$otpRecord) {
            return response()->json([
                'status' => 'not_requested',
                'message' => 'No OTP has been requested for this email'
            ], 200);
        }

        if ($otpRecord->isVerified()) {
            return response()->json([
                'status' => 'verified',
                'message' => 'Email is already verified',
                'verified' => true
            ], 200);
        }

        if ($otpRecord->isExpired()) {
            return response()->json([
                'status' => 'expired',
                'message' => 'OTP has expired',
                'verified' => false
            ], 200);
        }

        return response()->json([
            'status' => 'pending',
            'message' => 'Waiting for OTP verification',
            'email' => $data['email'],
            'attempts_used' => $otpRecord->attempts,
            'attempts_remaining' => 5 - $otpRecord->attempts,
            'verified' => false
        ], 200);
    }
}
