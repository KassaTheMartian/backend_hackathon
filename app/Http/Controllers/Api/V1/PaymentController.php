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
     * List all payments
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->service->list($request)
            ->through(fn($model) => PaymentResource::make($model));
        return $this->paginated($items, __('payments.list_retrieved'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/vnpay/create",
     *     summary="Create VNPay payment URL",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id", "amount"},
     *             @OA\Property(property="booking_id", type="integer"),
     *             @OA\Property(property="amount", type="integer"),
     *             @OA\Property(property="bank_code", type="string", enum={"","VNPAYQR","VNBANK","INTCARD"}),
     *             @OA\Property(property="language", type="string", enum={"vi","en"}),
     *             @OA\Property(property="guest_email", type="string"),
     *             @OA\Property(property="guest_phone", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=400, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * Tạo VNPay payment URL
     *
     * @param CreateVnpayPaymentRequest $request
     * @return JsonResponse
     */
    public function vnpayCreate(CreateVnpayPaymentRequest $request): JsonResponse
    {
        $v = $request->validated();
        $data = $this->service->vnpayCreate(
            $v['booking_id'],
            $v['amount'],
            $v['bank_code'] ?? null,
            $v['language'] ?? null,
            $v['guest_email'] ?? null,
            $v['guest_phone'] ?? null,
        );
        if (!$data['success']) {
            return response()->json($data, 400);
        }
        return $this->ok($data, __('payments.url_created'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/payments/vnpay/return",
     *     summary="Handle VNPay return URL",
     *     tags={"Payments"},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * Xử lý return URL từ VNPAY
     *
     * @param VnpayReturnRequest $request
     * @return JsonResponse
     */
    public function vnpayReturn(VnpayReturnRequest $request): JsonResponse
    {
        $result = $this->service->vnpayReturn($request->all());
        return $this->ok($result, $result['success'] ? __('payments.processed_success') : __('payments.processed_failed'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/vnpay/ipn",
     *     summary="Handle VNPay IPN",
     *     tags={"Payments"},
     *     @OA\Response(response=200, description="OK")
     * )
     * Nhận IPN từ VNPAY
     *
     * @param VnpayIpnRequest $request
     * @return JsonResponse
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
     * Refund giao dịch VNPay
     *
     * @param VnpayRefundRequest $request
     * @return JsonResponse
     */
    public function vnpayRefund(VnpayRefundRequest $request): JsonResponse
    {
        $v = $request->validated();
        $result = $this->service->vnpayRefund(
            $v['transaction_id'],
            $v['amount'],
            $v['reason'],
            $v['guest_email'] ?? null,
            $v['guest_phone'] ?? null,
        );
        return $this->ok($result, __('payments.refund_success'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payments/vnpay/query",
     *     summary="Query VNPay transaction",
     *     tags={"Payments"},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * Query trạng thái giao dịch VNPay
     *
     * @param VnpayQueryRequest $request
     * @return JsonResponse
     */
    public function vnpayQuery(VnpayQueryRequest $request): JsonResponse
    {
        $v = $request->validated();
        $result = $this->service->vnpayQuery(
            $v['transaction_id'],
            $v['guest_email'] ?? null,
            $v['guest_phone'] ?? null,
        );
        return $this->ok($result, __('payments.query_success'));
    }
}
