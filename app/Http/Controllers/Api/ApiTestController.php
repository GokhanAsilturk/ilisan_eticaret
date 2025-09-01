<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiTestController extends Controller
{
    /**
     * Temel API test endpoint'i
     *
     * @OA\Get(
     *     path="/api/api-test/ping",
     *     tags={"API Test"},
     *     summary="Test API connectivity",
     *     description="Basic endpoint to test API connectivity and status",
     *     @OA\Response(
     *         response=200,
     *         description="API is working",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="API is working"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="environment", type="string", example="local")
     *         )
     *     )
     * )
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'API is working',
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
        ]);
    }

    /**
     * Authentication header test
     */
    public function authTest(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Auth headers received',
            'authorization_header' => $request->header('Authorization'),
            'accept_header' => $request->header('Accept'),
            'content_type' => $request->header('Content-Type'),
            'user_agent' => $request->header('User-Agent'),
        ]);
    }

    /**
     * POST data test
     */
    public function postTest(Request $request): JsonResponse
    {
        $data = $request->all();

        return response()->json([
            'status' => 'success',
            'message' => 'POST data received',
            'received_data' => $data,
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
        ]);
    }

    /**
     * JSON validation test
     */
    public function validationTest(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'age' => 'nullable|integer|min:0|max:150'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Validation passed',
            'validated_data' => $request->only(['name', 'email', 'age']),
        ]);
    }

    /**
     * Error test
     */
    public function errorTest(): JsonResponse
    {
        abort(500, 'This is a test error');
    }
}
