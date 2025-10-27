<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DemoController as V1DemoController;
use App\Http\Controllers\Api\V1\AuthController as V1AuthController;
use App\Http\Controllers\Api\V1\UserController as V1UserController;
use App\Http\Controllers\Api\V1\ServiceController as V1ServiceController;
use App\Http\Controllers\Api\V1\BranchController as V1BranchController;
use App\Http\Controllers\Api\V1\BookingController as V1BookingController;
use App\Http\Controllers\Api\V1\PaymentController as V1PaymentController;
use App\Http\Controllers\Api\V1\ReviewController as V1ReviewController;
use App\Http\Controllers\Api\V1\PostController as V1PostController;
use App\Http\Controllers\Api\V1\ContactController as V1ContactController;
use App\Http\Controllers\Api\V1\ChatbotController as V1ChatbotController;
use App\Http\Controllers\Api\V1\ProfileController as V1ProfileController;

// Apply rate limiting and logging middleware to all API routes
Route::middleware(['throttle:api'])->group(function () {

    // API Version 1 - 60 requests per minute
    Route::prefix('v1')->middleware(['throttle:60,1'])->group(function () {
        // Auth
        Route::post('/auth/login', [V1AuthController::class, 'login']);
        Route::post('/auth/register', [V1AuthController::class, 'register']);
        Route::post('/auth/forgot-password', [V1AuthController::class, 'forgotPassword']);
        Route::post('/auth/reset-password', [V1AuthController::class, 'resetPassword']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/auth/me', [V1AuthController::class, 'me']);
            Route::post('/auth/logout', [V1AuthController::class, 'logout']);
            Route::post('/auth/logout-all', [V1AuthController::class, 'logoutAll']);
        });

        // Public routes
        Route::get('/services', [V1ServiceController::class, 'index']);
        Route::get('/services/{service}', [V1ServiceController::class, 'show']);
        Route::get('/service-categories', [V1ServiceController::class, 'categories']);
        
        Route::get('/branches', [V1BranchController::class, 'index']);
        Route::get('/branches/{branch}', [V1BranchController::class, 'show']);
        Route::get('/branches/{branch}/available-slots', [V1BranchController::class, 'availableSlots']);
        
        Route::get('/reviews', [V1ReviewController::class, 'index']);
        Route::get('/reviews/{review}', [V1ReviewController::class, 'show']);
        
        Route::get('/posts', [V1PostController::class, 'index']);
        Route::get('/posts/{post}', [V1PostController::class, 'show']);
        
        Route::post('/contact', [V1ContactController::class, 'store']);
        
        Route::post('/chatbot/message', [V1ChatbotController::class, 'sendMessage']);
        Route::get('/chatbot/session/{sessionId}', [V1ChatbotController::class, 'getHistory']);

        // Authenticated routes
        Route::middleware('auth:sanctum')->group(function () {
            // Bookings
            Route::get('/bookings', [V1BookingController::class, 'index']);
            Route::post('/bookings', [V1BookingController::class, 'store']);
            Route::get('/bookings/{booking}', [V1BookingController::class, 'show']);
            Route::put('/bookings/{booking}', [V1BookingController::class, 'update']);
            Route::post('/bookings/{booking}/cancel', [V1BookingController::class, 'cancel']);
            Route::get('/my-bookings', [V1BookingController::class, 'myBookings']);
            
            // Payments
            Route::post('/payments/create-intent', [V1PaymentController::class, 'createIntent']);
            Route::post('/payments/confirm', [V1PaymentController::class, 'confirm']);
            Route::post('/payments/webhook', [V1PaymentController::class, 'webhook']);
            
            // Reviews
            Route::post('/reviews', [V1ReviewController::class, 'store']);
            
            // Profile
            Route::get('/profile', [V1ProfileController::class, 'show']);
            Route::put('/profile', [V1ProfileController::class, 'update']);
            Route::put('/profile/password', [V1ProfileController::class, 'changePassword']);
            Route::get('/profile/promotions', [V1ProfileController::class, 'promotions']);
        });

        // Admin routes
        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            // Services management
            Route::post('/services', [V1ServiceController::class, 'store']);
            Route::put('/services/{service}', [V1ServiceController::class, 'update']);
            Route::delete('/services/{service}', [V1ServiceController::class, 'destroy']);
            
            // Users management
            Route::get('/users', [V1UserController::class, 'index']);
            Route::get('/users/{id}', [V1UserController::class, 'show']);
            Route::put('/users/{id}', [V1UserController::class, 'update']);
            Route::delete('/users/{id}', [V1UserController::class, 'destroy']);
        });

        // Demos (existing)
        Route::get('/demos', [V1DemoController::class, 'index']);
        Route::get('/demos/{id}', [V1DemoController::class, 'show']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/demos', [V1DemoController::class, 'store']);
            Route::put('/demos/{id}', [V1DemoController::class, 'update']);
            Route::delete('/demos/{id}', [V1DemoController::class, 'destroy']);
        });
    });

});


