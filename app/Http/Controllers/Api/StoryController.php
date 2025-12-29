<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function store(Request $request)
    {
        return response()->json(['story_id' => null, 'message' => 'Create story (stub)'], 201);
    }

    public function feed(Request $request)
    {
        return response()->json(['stories' => [], 'message' => 'Stories feed (stub)']);
    }

    public function view($id)
    {
        return response()->json(['viewed' => true]);
    }
}
