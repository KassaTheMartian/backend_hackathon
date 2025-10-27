<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\PaymentResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Create a new PaymentController instance.
     *
     * @param PaymentServiceInterface $service The payment service
     */
    public function __construct(private readonly PaymentServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/intent",
     *     summary="Create payment intent",
     *     tags={"Payments"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id"},
     *             @OA\Property(property="booking_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Create payment intent for Stripe.
     *
     * @param Request $request The create payment intent request
     * @return JsonResponse The payment intent response
     */
    public function createIntent(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id'
        ]);
        
        $booking = Booking::findOrFail($request->booking_id);
        $this->authorize('view', $booking);
        
        $paymentIntent = $this->service->createPaymentIntent($booking);
        
        return $this->ok($paymentIntent, 'Payment intent created successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/confirm",
     *     summary="Confirm payment",
     *     tags={"Payments"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id", "payment_intent_id", "payment_method"},
     *             @OA\Property(property="booking_id", type="integer"),
     *             @OA\Property(property="payment_intent_id", type="string"),
     *             @OA\Property(property="payment_method", type="string", enum={"stripe", "card", "online"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Confirm payment.
     *
     * @param Request $request The confirm payment request
     * @return JsonResponse The payment confirmation response
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'payment_intent_id' => 'required|string',
            'payment_method' => 'required|string|in:stripe,card,online'
        ]);
        
        $booking = Booking::findOrFail($request->booking_id);
        $this->authorize('view', $booking);
        
        $payment = $this->service->confirmPayment(
            $booking,
            $request->payment_intent_id,
            $request->payment_method
        );
        
        return $this->ok(PaymentResource::make($payment), 'Payment confirmed successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/webhook",
     *     summary="Handle Stripe webhook",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Handle Stripe webhook.
     *
     * @param Request $request The webhook request
     * @return JsonResponse The webhook response
     */
    public function webhook(Request $request): JsonResponse
    {
        $this->service->handleWebhook($request);
        
        return $this->ok(null, 'Webhook processed successfully');
    }
}