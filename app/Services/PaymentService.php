<?php

namespace App\Services;

use App\Models\Payment;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling payment operations.
 *
 * Manages VNPay payment processing, refunds, and transaction queries.
 */
class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected BookingRepositoryInterface $bookingRepository
    ) {
    }
    /**
     * List payments with filters and scoping.
     *
     * @param Request $request The HTTP request.
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $filters = [
            'per_page' => (int)($request->query('per_page', 15)),
        ];
        
        if ($request->user()) {
            $filters['user_id'] = $request->user()->id;
        }
        if ($status = $request->query('status')) {
            $filters['status'] = $status;
        }
        if ($method = $request->query('payment_method')) {
            $filters['payment_method'] = $method;
        }
        
        return $this->paymentRepository->getWithFilters($filters);
    }
    // Removed Stripe-related methods (createPaymentIntent, confirmPayment, webhook)

    /**
     * VNPay helpers
     */
    /**
     * Generate VNPay hash.
     *
     * @param array $params The parameters.
     * @return string
     */
    protected function vnpHash(array $params): string
    {
        ksort($params);
        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = $key . '=' . rawurlencode((string)$value);
        }
        $data = implode('&', $pairs);
        return hash_hmac('sha512', $data, (string)config('vnpay.hash_secret'));
    }

    /**
     * Get base VNPay parameters.
     *
     * @param string $txnRef The transaction reference.
     * @param int $amount The amount.
     * @param string|null $bankCode The bank code.
     * @param string|null $language The language.
     * @return array
     */
    protected function vnpBaseParams(string $txnRef, int $amount, ?string $bankCode = null, ?string $language = 'vi'): array
    {
        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => config('vnpay.tmn_code'),
            'vnp_Amount' => $amount * 100,
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => __('payments.order_info', ['txn_ref' => $txnRef]),
            'vnp_OrderType' => 'other',
            'vnp_Locale' => $language ?: 'vi',
            'vnp_ReturnUrl' => config('vnpay.return_url'),
            'vnp_IpAddr' => request()->ip(),
            'vnp_CreateDate' => now()->format('YmdHis'),
            'vnp_ExpireDate' => now()->addMinutes(15)->format('YmdHis'),
        ];
        if (!empty($bankCode) || $bankCode === '') {
            $params['vnp_BankCode'] = $bankCode;
        }
        return $params;
    }

    /**
     * Create VNPay payment URL and record.
     *
     * @param int $bookingId The booking ID.
     * @param int $amount The payment amount.
     * @param string|null $bankCode The bank code.
     * @param string|null $language The language.
     * @param string|null $guestEmail The guest email.
     * @param string|null $guestPhone The guest phone.
     * @return array
     */
    public function vnpayCreate(int $bookingId, int|string $amount, ?string $bankCode, ?string $language, ?string $guestEmail, ?string $guestPhone): array
    {
        $amount = (int) $amount;
        // Check valid booking
        $booking = $this->bookingRepository->find($bookingId);
        if (!$booking) {
            return [
                'success' => false,
                'error' => __('payments.booking_not_found'),
            ];
        }
        // Check valid amount
        if ((int)$amount <= 0) {
            return [
                'success' => false,
                'error' => __('payments.invalid_amount'),
            ];
        }
        $tmnCode = config('vnpay.tmn_code');
        $vnpHashSecret = config('vnpay.hash_secret');
        $vnpUrlBase = config('vnpay.url');
        $returnUrl = config('vnpay.return_url');
        $orderInfo = __('payments.order_info', ['txn_ref' => $bookingId]);
        $txnRef = (string)$bookingId;
        $locale = $language ?: 'vi';
        $currDate = date('YmdHis');
        $expireDate = date('YmdHis', strtotime('+15 minutes'));
        $ipAddr = request()->ip() ?? '127.0.0.1';
        $inputData = [
            'vnp_Version'    => '2.1.0',
            'vnp_TmnCode'    => $tmnCode,
            'vnp_Amount'     => $amount * 100,
            'vnp_Command'    => 'pay',
            'vnp_CreateDate' => $currDate,
            'vnp_CurrCode'   => 'VND',
            'vnp_ExpireDate' => $expireDate,
            'vnp_IpAddr'     => $ipAddr,
            'vnp_Locale'     => $locale,
            'vnp_OrderInfo'  => $orderInfo,
            'vnp_OrderType'  => 'other',
            'vnp_ReturnUrl'  => $returnUrl,
            'vnp_TxnRef'     => $txnRef,
        ];
        if ($bankCode) {
            $inputData['vnp_BankCode'] = $bankCode;
        }
        ksort($inputData);
        
        $query = "";
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        $vnpUrl = $vnpUrlBase . "?" . $query;
        if (isset($vnpHashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashData, $vnpHashSecret);
            $vnpUrl .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        $this->paymentRepository->create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'currency' => 'VND',
            'payment_method' => 'cash',
            'status' => 'pending',
            'transaction_id' => $txnRef,
            'metadata' => [
                'bank_code' => $bankCode,
                'language' => $language,
                'guest_email' => $guestEmail,
                'guest_phone' => $guestPhone,
            ],
        ]);

        return [
            'success' => true,
            'url' => $vnpUrl,
        ];
    }

    /**
     * Handle VNPay return URL.
     *
     * @param array $params The parameters.
     * @return array
     */
    public function vnpayReturn(array $params): array
    {
        // $secureHash = $params['vnp_SecureHash'] ?? '';
        // $checkParams = $params;
        // unset($checkParams['vnp_SecureHash'], $checkParams['vnp_SecureHashType']);
        // $computed = $this->vnpHash($checkParams);
        // if (strtolower($secureHash) !== strtolower($computed)) {
        //     return ['success' => false, 'message' => __('payments.invalid_signature')];
        // }

        $txnRef = $params['vnp_TxnRef'];
        $payment = $this->paymentRepository->findByTransactionId($txnRef);
        if (!$payment) {
            return ['success' => false, 'message' => __('payments.transaction_not_found')];
        }

        // TMN and amount checks
        if (($params['vnp_TmnCode'] ?? null) !== config('vnpay.tmn_code')) {
            return ['success' => false, 'message' => __('payments.invalid_merchant')];
        }
        if ((int)($params['vnp_Amount'] ?? 0) !== ((int)$payment->amount * 100)) {
            return ['success' => false, 'message' => __('payments.invalid_amount')];
        }

        // Idempotency
        if (in_array($payment->status, ['completed', 'refunded'])) {
            return ['success' => true, 'payment' => $payment];
        }

        $success = ($params['vnp_ResponseCode'] ?? null) === '00';
        $this->paymentRepository->update($payment->id, [
            'status' => $success ? 'completed' : 'failed',
        ]);
        
        // Refresh payment model
        $payment = $this->paymentRepository->find($payment->id);

        if ($success) {
            $booking = $payment->booking;
            if ($booking) {
                $this->bookingRepository->update($booking->id, [
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                ]);
            }
        }

        return ['success' => $success, 'payment' => $payment];
    }

    /**
     * Handle VNPay IPN notification.
     *
     * @param array $params The parameters.
     * @return array
     */
    public function vnpayIpn(array $params): array
    {
        $secureHash = $params['vnp_SecureHash'] ?? '';
        $checkParams = $params;
        unset($checkParams['vnp_SecureHash'], $checkParams['vnp_SecureHashType']);
        $computed = $this->vnpHash($checkParams);
        if (strtolower($secureHash) !== strtolower($computed)) {
            return ['RspCode' => '97', 'Message' => __('payments.invalid_checksum')];
        }

        $txnRef = $params['vnp_TxnRef'];
        $payment = $this->paymentRepository->findByTransactionId($txnRef);
        if (!$payment) {
            return ['RspCode' => '01', 'Message' => __('payments.order_not_found')];
        }

        if (($params['vnp_TmnCode'] ?? null) !== config('vnpay.tmn_code')) {
            return ['RspCode' => '03', 'Message' => __('payments.invalid_merchant')];
        }
        if ((int)($params['vnp_Amount'] ?? 0) !== ((int)$payment->amount * 100)) {
            return ['RspCode' => '04', 'Message' => __('payments.invalid_amount')];
        }

        // Idempotency
        if (in_array($payment->status, ['completed', 'refunded'])) {
            return ['RspCode' => '00', 'Message' => __('payments.confirm_success')];
        }

        $status = ($params['vnp_ResponseCode'] ?? null) === '00' ? 'completed' : 'failed';
        $this->paymentRepository->update($payment->id, ['status' => $status]);

        return ['RspCode' => '00', 'Message' => __('payments.confirm_success')];
    }

    /**
     * Refund VNPay transaction.
     *
     * @param string $transactionId The transaction ID.
     * @param int $amount The amount.
     * @param string $reason The reason.
     * @param string|null $guestEmail The guest email.
     * @param string|null $guestPhone The guest phone.
     * @return array
     */
    public function vnpayRefund(string $transactionId, int $amount, string $reason, ?string $guestEmail, ?string $guestPhone): array
    {
        // For hackathon scope, simulate refund success
        $payment = $this->paymentRepository->findByTransactionId($transactionId);
        if (!$payment) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(__('payments.transaction_not_found'));
        }
        
        $this->paymentRepository->update($payment->id, ['status' => 'refunded']);
        
        // Refresh payment model
        $payment = $this->paymentRepository->find($payment->id);
        
        return [
            'success' => true,
            'payment' => $payment,
            'gateway_response' => ['vnp_ResponseCode' => '00', 'vnp_ResponseMessage' => __('payments.success')],
        ];
    }

    /**
     * Query VNPay transaction status.
     *
     * @param string $transactionId The transaction ID.
     * @param string|null $guestEmail The guest email.
     * @param string|null $guestPhone The guest phone.
     * @return array
     */
    public function vnpayQuery(string $transactionId, ?string $guestEmail, ?string $guestPhone): array
    {
        $payment = $this->paymentRepository->findByTransactionId($transactionId);
        if (!$payment) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(__('payments.transaction_not_found'));
        }
        
        return [
            'success' => true,
            'payment' => $payment,
            'gateway_response' => [
                'vnp_ResponseCode' => $payment->status === 'completed' ? '00' : '02',
                'vnp_ResponseMessage' => $payment->status === 'completed' ? __('payments.success') : __('payments.pending_failed'),
                'vnp_TransactionStatus' => $payment->status === 'completed' ? '00' : '01',
            ],
        ];
    }
}
