<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TestController extends Controller
{
    public function test(): JsonResponse
    {
        return response()->json(['message' => 'Test API working', 'timestamp' => now()]);
    }
}
