<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'İlisan E-ticaret API',
        'version' => '1.0',
        'status' => 'running'
    ]);
});

// Health check endpoints
Route::get('/health', [HealthController::class, 'basic']);
Route::get('/health/detailed', [HealthController::class, 'detailed']);
Route::get('/status', [HealthController::class, 'basic']); // Alias
