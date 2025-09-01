<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\ApiTestController;
use App\Http\Controllers\Api\PaymentTestController;
use App\Http\Controllers\Api\v1\PaymentController;
use App\Http\Controllers\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API Health check
Route::get('/health', [HealthController::class, 'api']);

// Test endpoint
Route::get('/test', [TestController::class, 'test']);

// API Test endpoints (database independent)
Route::prefix('api-test')->group(function () {
    Route::get('/ping', [ApiTestController::class, 'ping']);
    Route::get('/auth-headers', [ApiTestController::class, 'authTest']);
    Route::post('/post-data', [ApiTestController::class, 'postTest']);
    Route::post('/validation', [ApiTestController::class, 'validationTest']);
    Route::get('/error', [ApiTestController::class, 'errorTest']);
});

// Payment Test endpoints (debug mode only)
Route::prefix('payment-test')->group(function () {
    Route::get('/cards', [PaymentTestController::class, 'getTestCards']);
    Route::get('/config', [PaymentTestController::class, 'getPaymentConfig']);
    Route::get('/sample-request', [PaymentTestController::class, 'getSamplePaymentRequest']);
});

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Product routes (public)
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/featured', [ProductController::class, 'featured']);
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/variant/{variant}', [ProductController::class, 'variant']);
    Route::get('/{product:slug}', [ProductController::class, 'show']);
});

// Cart routes (public with optional auth)
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'store']);
    Route::put('/{cartItem}', [CartController::class, 'update']);
    Route::delete('/{cartItem}', [CartController::class, 'destroy']);
    Route::delete('/', [CartController::class, 'clear']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::prefix('user')->group(function () {
        Route::get('/', [AuthController::class, 'me']);
        Route::put('/', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Address management
        Route::get('/addresses', [AuthController::class, 'addresses']);
        Route::post('/addresses', [AuthController::class, 'storeAddress']);
        Route::put('/addresses/{address}', [AuthController::class, 'updateAddress']);
        Route::delete('/addresses/{address}', [AuthController::class, 'deleteAddress']);
    });

    // Checkout routes
    Route::prefix('checkout')->group(function () {
        Route::post('/validate', [CheckoutController::class, 'validateCart']);
        Route::post('/shipping', [CheckoutController::class, 'calculateShipping']);
        Route::post('/order', [CheckoutController::class, 'createOrder']);
        Route::get('/order/{orderNumber}', [CheckoutController::class, 'getOrder']);
    });

    // Order management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{orderNumber}', [OrderController::class, 'show']);
        Route::post('/{orderNumber}/cancel', [OrderController::class, 'cancel']);
        Route::post('/{orderNumber}/reorder', [OrderController::class, 'reorder']);
    });

    // Payment routes
    Route::prefix('payment')->group(function () {
        Route::post('/initiate', [PaymentController::class, 'initiatePayment']);
        Route::get('/{paymentId}/status', [PaymentController::class, 'getPaymentStatus']);
        Route::post('/{paymentId}/refund', [PaymentController::class, 'requestRefund']);
    });
});

// Public payment callback routes (webhook)
Route::prefix('payment')->group(function () {
    Route::post('/iyzico/callback', [PaymentController::class, 'handle3DCallback'])->name('payment.iyzico.callback');
    Route::post('/iyzico/webhook', [PaymentController::class, 'handleWebhook'])->name('payment.iyzico.webhook');
});

// Fallback user route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
