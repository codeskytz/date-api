<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ApiToken;

class AuthenticateWithApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization') ?: $request->header('authorization');
        if (! $header || ! preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $plain = $matches[1];
        $hashed = hash('sha256', $plain);
        $token = ApiToken::where('token', $hashed)->first();
        if (! $token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // log in the user for the request
        Auth::loginUsingId($token->user_id);

        return $next($request);
    }
}
