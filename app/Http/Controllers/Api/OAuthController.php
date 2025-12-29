<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OAuthController extends Controller
{
    public function redirect($provider)
    {
        return response()->json(['message' => "Redirect to {$provider} (stub)"]);
    }

    public function callback(Request $request, $provider)
    {
        return response()->json(['message' => "Callback from {$provider} (stub)"]);
    }

    public function token(Request $request, $provider)
    {
        return response()->json(['message' => "Token exchange for {$provider} (stub)"]);
    }
}
