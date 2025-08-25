<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'İlisan E-ticaret API',
        'version' => '1.0',
        'status' => 'running'
    ]);
});
