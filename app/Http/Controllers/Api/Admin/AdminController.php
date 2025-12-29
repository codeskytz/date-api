<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        return response()->json(['token' => null, 'role' => 'admin']);
    }

    public function dashboard()
    {
        return response()->json(['users' => 0, 'posts' => 0]);
    }

    public function users()
    {
        return response()->json(['data' => []]);
    }

    public function banUser($id, Request $request)
    {
        return response()->json(['status' => 'banned']);
    }
}
