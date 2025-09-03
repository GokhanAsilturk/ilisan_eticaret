<?php

use Illuminate\Support\Facades\Route;

// API-only backend için basit redirect
Route::get('/', function () {
    return response()->json([
        'message' => 'İlisan E-Ticaret Backend API',
        'version' => '1.0.0',
        'documentation' => '/api/documentation',
        'admin' => '/admin',
        'api_health' => '/api/health'
    ]);
});

// Admin panel için route otomatik olarak Filament tarafından handle ediliyor

Route::get('/_blade-test', function () {
    return view('blade-test', ['ts' => now()->toDateTimeString()]);
});
