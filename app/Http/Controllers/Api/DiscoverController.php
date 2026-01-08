<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function users(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [],
        ], 200);
    }

    public function nearby(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [],
        ], 200);
    }
}
