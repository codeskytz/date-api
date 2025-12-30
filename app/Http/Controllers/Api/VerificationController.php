<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VerificationRequest;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    protected $mediaService;

    public function __construct()
    {
        $this->mediaService = new MediaStorageService();
    }

    /**
     * Submit a verification request
     * POST /api/verification/request
     */
    public function request(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        // Check if user has a pending verification request
        $pendingRequest = VerificationRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already have a pending verification request'
            ], 422);
        }

        $validated = $request->validate([
            'type' => 'required|in:identity,business,creator',
            'full_name' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'document_front' => 'required|string',
            'document_back' => 'required|string',
            'selfie' => 'required|string',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            // Store documents in private bucket
            $documentFront = $this->mediaService->storeVerificationDocument($request->document_front, 'identity-verification');
            $documentBack = $this->mediaService->storeVerificationDocument($request->document_back, 'identity-verification');
            $selfieUrl = $this->mediaService->storeVerificationDocument($request->selfie, 'identity-verification');

            // Create verification request
            $verificationRequest = VerificationRequest::create([
                'user_id' => $user->id,
                'type' => $validated['type'],
                'status' => 'pending',
                'full_name' => $validated['full_name'],
                'document_type' => $validated['document_type'],
                'document_front' => $documentFront,
                'document_back' => $documentBack,
                'selfie' => $selfieUrl,
                'note' => $validated['note'] ?? null,
            ]);

            return response()->json([
                'status' => 'submitted',
                'message' => 'Verification request submitted successfully',
                'request_id' => $verificationRequest->id,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit verification request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get verification status
     * GET /api/verification/status
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        // Check current verification status
        $verificationRequest = VerificationRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$verificationRequest) {
            return response()->json([
                'verified' => $user->verified ?? false,
                'status' => 'unverified',
                'type' => null,
                'submitted_at' => null,
            ]);
        }

        return response()->json([
            'verified' => $user->verified ?? false,
            'status' => $verificationRequest->status,
            'type' => $verificationRequest->type,
            'submitted_at' => $verificationRequest->created_at,
            'reviewed_at' => $verificationRequest->reviewed_at,
            'rejection_reason' => $verificationRequest->status === 'rejected' ? $verificationRequest->rejection_reason : null,
        ]);
    }

    /**
     * Cancel a verification request
     * DELETE /api/verification/cancel
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $verificationRequest = VerificationRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$verificationRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'No pending verification request found'
            ], 404);
        }

        try {
            // Delete stored documents
            if ($verificationRequest->document_front) {
                $this->mediaService->deleteFile($verificationRequest->document_front);
            }
            if ($verificationRequest->document_back) {
                $this->mediaService->deleteFile($verificationRequest->document_back);
            }
            if ($verificationRequest->selfie) {
                $this->mediaService->deleteFile($verificationRequest->selfie);
            }

            // Update status to cancelled
            $verificationRequest->update(['status' => 'cancelled']);

            return response()->json([
                'status' => 'cancelled',
                'message' => 'Verification request cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to cancel verification request: ' . $e->getMessage()
            ], 500);
        }
    }
}
