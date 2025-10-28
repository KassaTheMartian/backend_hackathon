<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Contracts\PaymentServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentService implements PaymentServiceInterface
{
    public function list(Request $request): LengthAwarePaginator
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
        return $query->latest('id')->paginate($perPage);
    }
    /**
     * Create payment intent for Stripe.
     */
    public function createPaymentIntent(Booking $booking): array
    {
        // In a real implementation, you would integrate with Stripe
        // For now, we'll return a mock response
        
        $paymentIntent = [
            'client_secret' => 'pi_mock_' . uniqid() . '_secret_' . uniqid(),
            'payment_intent_id' => 'pi_mock_' . uniqid(),
            'amount' => $booking->total_amount * 100, // Convert to cents
            'currency' => 'vnd',
        ];

        return $paymentIntent;
    }

    public function createPaymentIntentById(int $bookingId): array
    {
        $booking = Booking::findOrFail($bookingId);
        $user = request()->user();
        if ($user && $booking->user_id !== $user->id && !$user->isAdmin()) {
            throw new \Exception('You do not have permission to pay for this booking');
        }
        return $this->createPaymentIntent($booking);
    }

    /**
     * Confirm payment.
     */
    public function confirmPayment(Booking $booking, string $paymentIntentId, string $paymentMethod): Payment
    {
        // In a real implementation, you would verify with Stripe
        // For now, we'll create a mock payment record
        
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'currency' => 'VND',
            'payment_method' => $paymentMethod,
            'stripe_payment_intent_id' => $paymentIntentId,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        // Update booking status
        $booking->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        return $payment;
    }

    public function confirmPaymentById(int $bookingId, string $paymentIntentId, string $paymentMethod): Payment
    {
        $booking = Booking::findOrFail($bookingId);
        $user = request()->user();
        if ($user && $booking->user_id !== $user->id && !$user->isAdmin()) {
            throw new \Exception('You do not have permission to confirm this payment');
        }
        return $this->confirmPayment($booking, $paymentIntentId, $paymentMethod);
    }

    /**
     * Handle Stripe webhook.
     */
    public function handleWebhook($request): void
    {
        // In a real implementation, you would verify the webhook signature
        // and process the webhook events
        
        Log::info('Payment webhook received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);
    }

    /**
     * VNPay helpers
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

    protected function vnpBaseParams(string $txnRef, int $amount, ?string $bankCode = null, ?string $language = 'vi'): array
    {
        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => config('vnpay.tmn_code'),
            'vnp_Amount' => $amount * 100,
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $txnRef,
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

    public function vnpayCreate(int $bookingId, ?string $bankCode, ?string $language, ?string $guestEmail, ?string $guestPhone): array
    {
        $booking = Booking::findOrFail($bookingId);
        $txnRef = 'BK' . $booking->id . '_' . now()->format('YmdHis');
        $params = $this->vnpBaseParams($txnRef, (int)$booking->total_amount, $bankCode, $language);
        $hash = $this->vnpHash($params);
        $params['vnp_SecureHash'] = $hash;
        $url = rtrim(config('vnpay.url'), '?') . '?' . http_build_query($params);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_amount,
            'currency' => 'VND',
            'payment_method' => 'vnpay',
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
            'payment' => $payment,
            'vnpay_url' => $url,
        ];
    }

    public function vnpayReturn(array $params): array
    {
        $secureHash = $params['vnp_SecureHash'] ?? '';
        $checkParams = $params;
        unset($checkParams['vnp_SecureHash'], $checkParams['vnp_SecureHashType']);
        $computed = $this->vnpHash($checkParams);
        if (strtolower($secureHash) !== strtolower($computed)) {
            return ['success' => false, 'message' => 'Invalid signature'];
        }

        $txnRef = $params['vnp_TxnRef'];
        $payment = Payment::where('transaction_id', $txnRef)->first();
        if (!$payment) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        // TMN and amount checks
        if (($params['vnp_TmnCode'] ?? null) !== config('vnpay.tmn_code')) {
            return ['success' => false, 'message' => 'Invalid merchant'];
        }
        if ((int)($params['vnp_Amount'] ?? 0) !== ((int)$payment->amount * 100)) {
            return ['success' => false, 'message' => 'Invalid amount'];
        }

        // Idempotency
        if (in_array($payment->status, ['completed', 'refunded'])) {
            return ['success' => true, 'payment' => $payment];
        }

        $success = ($params['vnp_ResponseCode'] ?? null) === '00';
        $payment->update([
            'status' => $success ? 'completed' : 'failed',
        ]);

        if ($success) {
            $booking = $payment->booking;
            if ($booking) {
                $booking->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                ]);
            }
        }

        return ['success' => $success, 'payment' => $payment];
    }

    public function vnpayIpn(array $params): array
    {
        $secureHash = $params['vnp_SecureHash'] ?? '';
        $checkParams = $params;
        unset($checkParams['vnp_SecureHash'], $checkParams['vnp_SecureHashType']);
        $computed = $this->vnpHash($checkParams);
        if (strtolower($secureHash) !== strtolower($computed)) {
            return ['RspCode' => '97', 'Message' => 'Invalid Checksum'];
        }

        $txnRef = $params['vnp_TxnRef'];
        $payment = Payment::where('transaction_id', $txnRef)->first();
        if (!$payment) {
            return ['RspCode' => '01', 'Message' => 'Order not found'];
        }

        if (($params['vnp_TmnCode'] ?? null) !== config('vnpay.tmn_code')) {
            return ['RspCode' => '03', 'Message' => 'Invalid merchant'];
        }
        if ((int)($params['vnp_Amount'] ?? 0) !== ((int)$payment->amount * 100)) {
            return ['RspCode' => '04', 'Message' => 'Invalid amount'];
        }

        // Idempotency
        if (in_array($payment->status, ['completed', 'refunded'])) {
            return ['RspCode' => '00', 'Message' => 'Confirm Success'];
        }

        $status = ($params['vnp_ResponseCode'] ?? null) === '00' ? 'completed' : 'failed';
        $payment->update(['status' => $status]);

        return ['RspCode' => '00', 'Message' => 'Confirm Success'];
    }

    public function vnpayRefund(string $transactionId, int $amount, string $reason, ?string $guestEmail, ?string $guestPhone): array
    {
        // For hackathon scope, simulate refund success
        $payment = Payment::where('transaction_id', $transactionId)->firstOrFail();
        $payment->update(['status' => 'refunded']);
        return [
            'success' => true,
            'payment' => $payment,
            'gateway_response' => ['vnp_ResponseCode' => '00', 'vnp_ResponseMessage' => 'Success'],
        ];
    }

    public function vnpayQuery(string $transactionId, ?string $guestEmail, ?string $guestPhone): array
    {
        $payment = Payment::where('transaction_id', $transactionId)->firstOrFail();
        return [
            'success' => true,
            'payment' => $payment,
            'gateway_response' => [
                'vnp_ResponseCode' => $payment->status === 'completed' ? '00' : '02',
                'vnp_ResponseMessage' => $payment->status === 'completed' ? 'Success' : 'Pending/Failed',
                'vnp_TransactionStatus' => $payment->status === 'completed' ? '00' : '01',
            ],
        ];
    }
}
