<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        return response()->json(['message_id' => null, 'status' => 'sent']);
    }

    public function index($user_id)
    {
        return response()->json(['messages' => []]);
    }
}
