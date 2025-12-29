<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['data' => []]);
    }

    public function settings(Request $request)
    {
        return response()->json(['settings' => []]);
    }

    public function updateSettings(Request $request)
    {
        return response()->json(['message' => 'Settings updated (stub)']);
    }
}
