<?php

use App\Http\Controllers\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API Health check
Route::get('/health', [HealthController::class, 'api']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
