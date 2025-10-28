<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
// Removed Stripe requests
use App\Http\Requests\Payment\CreateVnpayPaymentRequest;
use App\Http\Requests\Payment\VnpayReturnRequest;
use App\Http\Requests\Payment\VnpayIpnRequest;
use App\Http\Requests\Payment\VnpayRefundRequest;
use App\Http\Requests\Payment\VnpayQueryRequest;
use App\Http\Resources\Payment\PaymentResource;
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

    // Removed Stripe createIntent endpoint

    // Removed Stripe confirm endpoint

    // Removed Stripe webhook endpoint

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
        $items = $this->service->list($request)
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
