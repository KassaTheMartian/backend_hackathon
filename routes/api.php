<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\V1\DemoController as V1DemoController;
use App\Http\Controllers\Api\V1\AuthController as V1AuthController;
use App\Http\Controllers\Api\V1\ServiceController as V1ServiceController;
use App\Http\Controllers\Api\V1\BranchController as V1BranchController;
use App\Http\Controllers\Api\V1\BookingController as V1BookingController;
use App\Http\Controllers\Api\V1\PaymentController as V1PaymentController;
use App\Http\Controllers\Api\V1\ReviewController as V1ReviewController;
use App\Http\Controllers\Api\V1\PostController as V1PostController;
use App\Http\Controllers\Api\V1\ContactController as V1ContactController;
use App\Http\Controllers\Api\V1\ChatbotController as V1ChatbotController;
use App\Http\Controllers\Api\V1\ChatRealTimeController as V1ChatRealTimeController;
use App\Http\Controllers\Api\V1\ProfileController as V1ProfileController;
use App\Http\Controllers\Api\V1\StaffController as V1StaffController;

// Apply locale + rate limiting middleware to all API routes
Route::middleware([\App\Http\Middleware\SetLocale::class, 'throttle:api'])->group(function () {

    // API Version 1 - 60 requests per minute
    Route::prefix('v1')->middleware(['throttle:60,1'])->group(function () {
        // Auth
        Route::post('/auth/login', [V1AuthController::class, 'login']);
        Route::post('/auth/register', [V1AuthController::class, 'register']);
        // Removed standalone send-otp route; OTP is sent within relevant flows
        Route::post('/auth/verify-otp', [V1AuthController::class, 'verifyOtp']);
        // Removed legacy token-based password reset routes; using OTP-only flow
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
        Route::get('/branches/{branch}/staff', action: [V1StaffController::class, 'byBranch']);
        
        Route::get('/staff', action: [V1StaffController::class, 'index']);

        Route::get('/reviews', action: [V1ReviewController::class, 'index']);
        Route::get('/reviews/{id}', action: [V1ReviewController::class, 'show']);

        Route::get('/posts', [V1PostController::class, 'index']);
        Route::get('/posts/featured', [V1PostController::class, 'featured']);
        Route::get('/posts/{id}', [V1PostController::class, 'show']);
        Route::get('/post-categories', [V1PostController::class, 'categories']);
        Route::get('/post-tags', [V1PostController::class, 'tags']);

        Route::post('/contact', [V1ContactController::class, 'store']);

        // Chat Real-time routes (guest and user)
        Route::post('/chat/guest/session', [V1ChatRealTimeController::class, 'createGuestSession']);
        Route::get('/chat/guest/{sessionId}/history', [V1ChatRealTimeController::class, 'getGuestHistory']);
        Route::post('/chat/guest/{sessionId}/message', [V1ChatRealTimeController::class, 'guestSendMessage']);
        Route::post('/chat/guest/{sessionId}/transfer-human', [V1ChatRealTimeController::class, 'transferToHuman']);
        Route::get('/chat/guest/{sessionId}/messages', [V1ChatRealTimeController::class, 'getNewMessages']);

        // Chatbot - available for both guests and authenticated users
        Route::post('/chatbot', [V1ChatbotController::class, 'chat']);

        Route::post('/bookings/{id}/cancel', [V1BookingController::class, 'cancel']);
        Route::post('/bookings/{id}/reschedule', [V1BookingController::class, 'reschedule']);
        Route::get('/availability', [V1BookingController::class, 'availability']);
        Route::post('/guest-booking/send-otp', [V1BookingController::class, 'sendGuestBookingOtp'])->middleware('throttle:otp');
        Route::get('/guest-bookings', [V1BookingController::class, 'guestBookings']);
        Route::post('/create-guest-bookings', [V1BookingController::class, 'createGuestBookings']);
        // Payments - list (scope to user)
        Route::middleware('auth:sanctum')->get('/payments', [V1PaymentController::class, 'index']);

        // Payments - VNPay
        Route::post('/payments/vnpay/create', [V1PaymentController::class, 'vnpayCreate']);
        Route::get('/payments/vnpay/return', [V1PaymentController::class, 'vnpayReturn']);
        Route::post('/payments/vnpay/ipn', [V1PaymentController::class, 'vnpayIpn']);
        Route::post('/payments/vnpay/refund', [V1PaymentController::class, 'vnpayRefund']);
        Route::post('/payments/vnpay/query', [V1PaymentController::class, 'vnpayQuery']);

        // Authenticated routes
        Route::middleware('auth:sanctum')->group(function () {
            // Bookings
            Route::post('/bookings', [V1BookingController::class, 'store']);
            Route::put('/bookings/{id}', [V1BookingController::class, 'update']);
            Route::get('/my-bookings', [V1BookingController::class, 'myBookings']);
            Route::get('/bookings/by-code/{code}', [V1BookingController::class, 'showByCode']);
            
            // Reviews
            Route::post('/reviews', [V1ReviewController::class, 'store']);
            Route::get('/reviews/pending', [V1ReviewController::class, 'pending']);
            Route::post('/reviews/{id}/approve', [V1ReviewController::class, 'approve']);
            Route::post('/reviews/{id}/reject', [V1ReviewController::class, 'reject']);
            Route::post('/reviews/{id}/respond', [V1ReviewController::class, 'respond']);

            // Chat staff routes
            Route::get('/chat/sessions/{id}/messages', [V1ChatRealTimeController::class, 'getSessionMessages']);
            Route::post('/chat/sessions/{id}/staff-message', [V1ChatRealTimeController::class, 'staffSendMessage']);

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

        // Demo routes removed
    });

});


