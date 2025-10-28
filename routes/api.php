<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DemoController as V1DemoController;
use App\Http\Controllers\Api\V1\AuthController as V1AuthController;
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
        Route::post('/auth/send-otp', [V1AuthController::class, 'sendOtp'])->middleware('throttle:otp');
        Route::post('/auth/verify-otp', [V1AuthController::class, 'verifyOtp']);
        Route::post('/auth/forgot-password', [V1AuthController::class, 'forgotPassword']);
        Route::post('/auth/reset-password', [V1AuthController::class, 'resetPassword']);
        Route::post('/auth/send-reset-otp', [V1AuthController::class, 'sendResetOtp'])->middleware('throttle:otp');
        Route::post('/auth/reset-password-otp', [V1AuthController::class, 'resetPasswordWithOtp']);
        Route::post('/auth/test-email', [V1AuthController::class, 'testEmail']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/auth/me', [V1AuthController::class, 'me']);
            Route::post('/auth/logout', [V1AuthController::class, 'logout']);
            Route::post('/auth/logout-all', [V1AuthController::class, 'logoutAll']);
        });

        // Public routes
        Route::get('/services', action: [V1ServiceController::class, 'index']);
        Route::get('/services/{id}', action: [V1ServiceController::class, 'show']);
        Route::get('/service-categories', action: [V1ServiceController::class, 'categories']);

        Route::get('/branches', action: [V1BranchController::class, 'index']);
        Route::get('/branches/{id}', action: [V1BranchController::class, 'show']);
        Route::get('/branches/{id}/available-slots', action: [V1BranchController::class, 'availableSlots']);

        Route::get('/reviews', action: [V1ReviewController::class, 'index']);
        Route::get('/reviews/{id}', action: [V1ReviewController::class, 'show']);

        Route::get('/posts', [V1PostController::class, 'index']);
        Route::get('/posts/featured', [V1PostController::class, 'featured']);
        Route::get('/posts/{id}', [V1PostController::class, 'show']);
        Route::get('/post-categories', [V1PostController::class, 'categories']);
        Route::get('/post-tags', [V1PostController::class, 'tags']);

        Route::post('/contact', [V1ContactController::class, 'store']);

        Route::post('/chatbot/message', [V1ChatbotController::class, 'sendMessage']);
        Route::get('/chatbot/sessions', [V1ChatbotController::class, 'sessions']);
        Route::post('/chatbot/sessions', [V1ChatbotController::class, 'createSession']);
        Route::get('/chatbot/sessions/{id}', [V1ChatbotController::class, 'show']);
        Route::delete('/chatbot/sessions/{id}', [V1ChatbotController::class, 'destroy']);
        Route::delete('/chatbot/sessions/{id}/messages', [V1ChatbotController::class, 'clearMessages']);

        // Bookings
        Route::get('/bookings', [V1BookingController::class, 'index']);
        Route::post('/bookings', [V1BookingController::class, 'store']);
        Route::get('/bookings/{id}', [V1BookingController::class, 'show']);
        Route::put('/bookings/{id}', [V1BookingController::class, 'update']);
        Route::post('/bookings/{id}/cancel', [V1BookingController::class, 'cancel']);
        Route::post('/bookings/{id}/reschedule', [V1BookingController::class, 'reschedule']);
        Route::get('/availability', [V1BookingController::class, 'availability']);
        Route::post('/guest-booking/send-otp', [V1BookingController::class, 'sendGuestBookingOtp'])->middleware('throttle:otp');
        Route::get('/guest-bookings', [V1BookingController::class, 'guestBookings']);

        // Payments - list (scope to user)
        Route::middleware('auth:sanctum')->get('/payments', [V1PaymentController::class, 'index']);

        // Payments - Stripe (protected)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/payments/create-intent', [V1PaymentController::class, 'createIntent']);
            Route::post('/payments/confirm', [V1PaymentController::class, 'confirm']);
        });
        Route::post('/payments/webhook', [V1PaymentController::class, 'webhook']);

        // Payments - VNPay
        Route::post('/payments/vnpay/create', [V1PaymentController::class, 'vnpayCreate']);
        Route::get('/payments/vnpay/return', [V1PaymentController::class, 'vnpayReturn']);
        Route::post('/payments/vnpay/ipn', [V1PaymentController::class, 'vnpayIpn']);
        Route::post('/payments/vnpay/refund', [V1PaymentController::class, 'vnpayRefund']);
        Route::post('/payments/vnpay/query', [V1PaymentController::class, 'vnpayQuery']);

        // Authenticated routes
        Route::middleware('auth:sanctum')->group(function () {
            // My Bookings
            Route::get('/my-bookings', [V1BookingController::class, 'myBookings']);
            
            // Reviews
            Route::post('/reviews', [V1ReviewController::class, 'store']);
            Route::get('/reviews/pending', [V1ReviewController::class, 'pending']);
            Route::post('/reviews/{id}/approve', [V1ReviewController::class, 'approve']);
            Route::post('/reviews/{id}/reject', [V1ReviewController::class, 'reject']);
            Route::post('/reviews/{id}/respond', [V1ReviewController::class, 'respond']);

            // Profile
            Route::get('/profile', [V1ProfileController::class, 'show']);
            Route::put('/profile', [V1ProfileController::class, 'update']);
            Route::put('/profile/password', [V1ProfileController::class, 'changePassword']);
            Route::post('/profile/avatar', [V1ProfileController::class, 'updateAvatar']);
            Route::delete('/profile/avatar', [V1ProfileController::class, 'deleteAvatar']);
            Route::put('/profile/language', [V1ProfileController::class, 'updateLanguage']);
            Route::get('/profile/stats', [V1ProfileController::class, 'stats']);
            Route::post('/profile/deactivate', [V1ProfileController::class, 'deactivate']);
            Route::get('/profile/promotions', [V1ProfileController::class, 'promotions']);
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


