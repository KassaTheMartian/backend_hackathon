<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\CreatePaymentIntentRequest;
use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Http\Requests\Payment\CreateVnpayPaymentRequest;
use App\Http\Requests\Payment\VnpayReturnRequest;
use App\Http\Requests\Payment\VnpayIpnRequest;
use App\Http\Requests\Payment\VnpayRefundRequest;
use App\Http\Requests\Payment\VnpayQueryRequest;
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
     *     path="/api/v1/payments/create-intent",
     *     summary="Create payment intent",
     *     tags={"Payments"},
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
     * @param CreatePaymentIntentRequest $request The create payment intent request
     * @return JsonResponse The payment intent response
     */
    public function createIntent(CreatePaymentIntentRequest $request): JsonResponse
    {
        $booking = Booking::findOrFail($request->booking_id);
        if ($request->user() && $booking->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return $this->forbidden('You do not have permission to pay for this booking');
        }
        
        $paymentIntent = $this->service->createPaymentIntent($booking);
        
        return $this->ok($paymentIntent, 'Payment intent created successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/confirm",
     *     summary="Confirm payment",
     *     tags={"Payments"},
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
     * @param ProcessPaymentRequest $request The confirm payment request
     * @return JsonResponse The payment confirmation response
     */
    public function confirm(ProcessPaymentRequest $request): JsonResponse
    {
        $booking = Booking::findOrFail($request->booking_id);
        if ($request->user() && $booking->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return $this->forbidden('You do not have permission to confirm this payment');
        }
        
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

    /**
     * @OA\Get(
     *     path="/api/v1/payments",
     *     summary="List payments",
     *     tags={"Payments"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"pending","completed","failed","refunded"})),
     *     @OA\Parameter(name="payment_method", in="query", @OA\Schema(type="string", enum={"vnpay","stripe","card","online"})),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display a listing of payments.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The paginated list of payments
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::query();
        if ($request->user()) {
            $query->whereHas('booking', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($method = $request->query('payment_method')) {
            $query->where('payment_method', $method);
        }
        $perPage = (int)($request->query('per_page', 15));
        $items = $query->latest('id')->paginate($perPage)
            ->through(fn ($model) => PaymentResource::make($model));

        return $this->paginated($items, 'Payments retrieved successfully');
    }

    /**
     * @OA: VNPay endpoints
     */

    /**
     * @OA\Post(
     *     path="/api/v1/payments/vnpay/create",
     *     summary="Create VNPay payment URL",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id"},
     *             @OA\Property(property="booking_id", type="integer"),
     *             @OA\Property(property="bank_code", type="string", enum={"","VNPAYQR","VNBANK","INTCARD"}),
     *             @OA\Property(property="language", type="string", enum={"vi","en"}),
     *             @OA\Property(property="guest_email", type="string"),
     *             @OA\Property(property="guest_phone", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function vnpayCreate(CreateVnpayPaymentRequest $request): JsonResponse
    {
        $data = $this->service->vnpayCreate(
            $request->integer('booking_id'),
            $request->input('bank_code'),
            $request->input('language'),
            $request->input('guest_email'),
            $request->input('guest_phone'),
        );
        return $this->ok($data, 'Payment URL created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/payments/vnpay/return",
     *     summary="Handle VNPay return URL",
     *     tags={"Payments"},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function vnpayReturn(VnpayReturnRequest $request): JsonResponse
    {
        $result = $this->service->vnpayReturn($request->all());
        return $this->ok($result, $result['success'] ? 'Payment processed successfully' : 'Payment failed');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/vnpay/ipn",
     *     summary="Handle VNPay IPN",
     *     tags={"Payments"},
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function vnpayIpn(VnpayIpnRequest $request): JsonResponse
    {
        $result = $this->service->vnpayIpn($request->all());
        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/vnpay/refund",
     *     summary="Refund VNPay transaction",
     *     tags={"Payments"},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function vnpayRefund(VnpayRefundRequest $request): JsonResponse
    {
        $result = $this->service->vnpayRefund(
            $request->string('transaction_id'),
            (int)$request->input('amount'),
            $request->string('reason'),
            $request->input('guest_email'),
            $request->input('guest_phone'),
        );
        return $this->ok($result, 'Refund processed successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/vnpay/query",
     *     summary="Query VNPay transaction",
     *     tags={"Payments"},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function vnpayQuery(VnpayQueryRequest $request): JsonResponse
    {
        $result = $this->service->vnpayQuery(
            $request->string('transaction_id'),
            $request->input('guest_email'),
            $request->input('guest_phone'),
        );
        return $this->ok($result, 'Transaction query successful');
    }
}
