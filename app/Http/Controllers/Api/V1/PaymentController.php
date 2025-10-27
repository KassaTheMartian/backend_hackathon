<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\PaymentResource;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Create payment intent for Stripe.
     */
    public function createIntent(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::findOrFail($request->booking_id);
        $paymentIntent = $this->paymentService->createPaymentIntent($booking);
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $paymentIntent,
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Confirm payment.
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_intent_id' => 'required|string',
            'payment_method' => 'required|string|in:stripe,card,online',
        ]);

        $booking = Booking::findOrFail($request->booking_id);
        $payment = $this->paymentService->confirmPayment(
            $booking,
            $request->payment_intent_id,
            $request->payment_method
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed successfully',
            'data' => new PaymentResource($payment),
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Handle Stripe webhook.
     */
    public function webhook(Request $request): JsonResponse
    {
        $this->paymentService->handleWebhook($request);
        
        return response()->json([
            'success' => true,
            'message' => 'Webhook processed successfully',
            'data' => null,
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}