<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DemoController as V1DemoController;
use App\Http\Controllers\Api\V1\AuthController as V1AuthController;
use App\Http\Controllers\Api\V1\UserController as V1UserController;

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

        // Demos
        Route::get('/demos', [V1DemoController::class, 'index']);
        Route::get('/demos/{id}', [V1DemoController::class, 'show']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/demos', [V1DemoController::class, 'store']);
            Route::put('/demos/{id}', [V1DemoController::class, 'update']);
            Route::delete('/demos/{id}', [V1DemoController::class, 'destroy']);
        });

        // Users (Admin only)
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/users', [V1UserController::class, 'index']);
            Route::get('/users/{id}', [V1UserController::class, 'show']);
            Route::put('/users/{id}', [V1UserController::class, 'update']);
            Route::delete('/users/{id}', [V1UserController::class, 'destroy']);
        });
    });

});


