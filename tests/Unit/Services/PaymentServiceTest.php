<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\PaymentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    private $paymentRepository;
    private $bookingRepository;
    private $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentRepository = Mockery::mock(PaymentRepositoryInterface::class);
        $this->bookingRepository = Mockery::mock(BookingRepositoryInterface::class);

        $this->paymentService = new PaymentService(
            $this->paymentRepository,
            $this->bookingRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ========== list() Tests ==========

    public function test_list_returns_paginated_payments_for_authenticated_user(): void
    {
        $user = Mockery::mock('stdClass');
        $user->id = 1;

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('user')->andReturn($user);
        $request->shouldReceive('query')->with('per_page', 15)->andReturn(15);
        $request->shouldReceive('query')->with('status')->andReturn(null);
        $request->shouldReceive('query')->with('payment_method')->andReturn(null);

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->paymentRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with([
                'per_page' => 15,
                'user_id' => 1,
            ])
            ->andReturn($paginator);

        $result = $this->paymentService->list($request);

        $this->assertSame($paginator, $result);
    }

    public function test_list_filters_by_status(): void
    {
        $user = Mockery::mock('stdClass');
        $user->id = 1;

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('user')->andReturn($user);
        $request->shouldReceive('query')->with('per_page', 15)->andReturn(15);
        $request->shouldReceive('query')->with('status')->andReturn('completed');
        $request->shouldReceive('query')->with('payment_method')->andReturn(null);

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->paymentRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with([
                'per_page' => 15,
                'user_id' => 1,
                'status' => 'completed',
            ])
            ->andReturn($paginator);

        $result = $this->paymentService->list($request);

        $this->assertSame($paginator, $result);
    }

    public function test_list_filters_by_payment_method(): void
    {
        $user = Mockery::mock('stdClass');
        $user->id = 1;

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('user')->andReturn($user);
        $request->shouldReceive('query')->with('per_page', 15)->andReturn(20);
        $request->shouldReceive('query')->with('status')->andReturn(null);
        $request->shouldReceive('query')->with('payment_method')->andReturn('vnpay');

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->paymentRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with([
                'per_page' => 20,
                'user_id' => 1,
                'payment_method' => 'vnpay',
            ])
            ->andReturn($paginator);

        $result = $this->paymentService->list($request);

        $this->assertSame($paginator, $result);
    }

    public function test_list_without_authenticated_user(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('user')->andReturn(null);
        $request->shouldReceive('query')->with('per_page', 15)->andReturn(15);
        $request->shouldReceive('query')->with('status')->andReturn(null);
        $request->shouldReceive('query')->with('payment_method')->andReturn(null);

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->paymentRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with([
                'per_page' => 15,
            ])
            ->andReturn($paginator);

        $result = $this->paymentService->list($request);

        $this->assertSame($paginator, $result);
    }

    // ========== vnpayCreate() Tests ==========

    public function test_vnpay_create_successfully_creates_payment(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        Config::set('vnpay.return_url', 'http://localhost/payment/return');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->id = 123;
        $booking->total_amount = 500000;

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->id = 1;

        $this->bookingRepository
            ->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($booking);

        $this->paymentRepository
            ->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($data) use ($payment) {
                $this->assertEquals(123, $data['booking_id']);
                $this->assertEquals(500000, $data['amount']);
                $this->assertEquals('VND', $data['currency']);
                $this->assertEquals('vnpay', $data['payment_method']);
                $this->assertEquals('pending', $data['status']);
                $this->assertStringStartsWith('BK123_', $data['transaction_id']);
                $this->assertEquals('NCB', $data['metadata']['bank_code']);
                return $payment;
            });

        $result = $this->paymentService->vnpayCreate(123, 'NCB', 'vi', null, null);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('vnpay_url', $result);
        $this->assertSame($payment, $result['payment']);
        $this->assertStringContainsString('https://sandbox.vnpayment.vn/paymentv2/vpcpay.html', $result['vnpay_url']);
        $this->assertStringContainsString('vnp_SecureHash=', $result['vnpay_url']);
    }

    public function test_vnpay_create_throws_exception_when_booking_not_found(): void
    {
        $this->bookingRepository
            ->shouldReceive('find')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        $this->paymentService->vnpayCreate(999, null, 'vi', null, null);
    }

    public function test_vnpay_create_with_guest_email_and_phone(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        Config::set('vnpay.return_url', 'http://localhost/payment/return');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->id = 456;
        $booking->total_amount = 300000;

        $payment = Mockery::mock(Payment::class)->makePartial();

        $this->bookingRepository
            ->shouldReceive('find')
            ->once()
            ->with(456)
            ->andReturn($booking);

        $this->paymentRepository
            ->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($data) use ($payment) {
                $this->assertEquals('guest@example.com', $data['metadata']['guest_email']);
                $this->assertEquals('0123456789', $data['metadata']['guest_phone']);
                return $payment;
            });

        $result = $this->paymentService->vnpayCreate(456, null, 'en', 'guest@example.com', '0123456789');

        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('vnpay_url', $result);
    }

    // ========== vnpayReturn() Tests ==========

    public function test_vnpay_return_successfully_processes_completed_payment(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->id = 1;
        $payment->amount = 500000;
        $payment->status = 'pending';

        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->id = 123;

        $payment->shouldReceive('getAttribute')
            ->with('booking')
            ->andReturn($booking);

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $this->paymentRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['status' => 'completed']);

        $this->paymentRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($payment);

        $this->bookingRepository
            ->shouldReceive('update')
            ->once()
            ->with(123, [
                'payment_status' => 'paid',
                'status' => 'confirmed',
            ]);

        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_Amount' => '50000000',
            'vnp_ResponseCode' => '00',
            'vnp_TmnCode' => 'TEST_TMN',
        ];

        // Generate valid hash
        $checkParams = $params;
        $hash = $this->generateVnpHash($checkParams);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayReturn($params);

        $this->assertTrue($result['success']);
        $this->assertSame($payment, $result['payment']);
    }

    public function test_vnpay_return_fails_with_invalid_signature(): void
    {
        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_SecureHash' => 'invalid_hash',
        ];

        $result = $this->paymentService->vnpayReturn($params);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('signature', strtolower($result['message']));
    }

    public function test_vnpay_return_fails_when_payment_not_found(): void
    {
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK999_20231029120000')
            ->andReturn(null);

        $params = [
            'vnp_TxnRef' => 'BK999_20231029120000',
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayReturn($params);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', strtolower($result['message']));
    }

    public function test_vnpay_return_fails_with_invalid_merchant(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->amount = 500000;

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_TmnCode' => 'WRONG_TMN',
            'vnp_Amount' => '50000000',
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayReturn($params);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('merchant', strtolower($result['message']));
    }

    public function test_vnpay_return_fails_with_invalid_amount(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->amount = 500000;

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_TmnCode' => 'TEST_TMN',
            'vnp_Amount' => '99999999', // Wrong amount
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayReturn($params);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('amount', strtolower($result['message']));
    }

    public function test_vnpay_return_handles_idempotency_for_completed_payment(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->amount = 500000;
        $payment->status = 'completed';

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_TmnCode' => 'TEST_TMN',
            'vnp_Amount' => '50000000',
            'vnp_ResponseCode' => '00',
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayReturn($params);

        $this->assertTrue($result['success']);
        $this->assertSame($payment, $result['payment']);
    }

    public function test_vnpay_return_processes_failed_payment(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->id = 1;
        $payment->amount = 500000;
        $payment->status = 'pending';

        $payment->shouldReceive('getAttribute')
            ->with('booking')
            ->andReturn(null);

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $this->paymentRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['status' => 'failed']);

        $this->paymentRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($payment);

        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_Amount' => '50000000',
            'vnp_ResponseCode' => '24', // Failed response code
            'vnp_TmnCode' => 'TEST_TMN',
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayReturn($params);

        $this->assertFalse($result['success']);
        $this->assertSame($payment, $result['payment']);
    }

    // ========== vnpayIpn() Tests ==========

    public function test_vnpay_ipn_successfully_confirms_payment(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->id = 1;
        $payment->amount = 500000;
        $payment->status = 'pending';

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $this->paymentRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['status' => 'completed']);

        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_Amount' => '50000000',
            'vnp_ResponseCode' => '00',
            'vnp_TmnCode' => 'TEST_TMN',
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayIpn($params);

        $this->assertEquals('00', $result['RspCode']);
        $this->assertStringContainsString('success', strtolower($result['Message']));
    }

    public function test_vnpay_ipn_returns_error_for_invalid_checksum(): void
    {
        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_SecureHash' => 'invalid_hash',
        ];

        $result = $this->paymentService->vnpayIpn($params);

        $this->assertEquals('97', $result['RspCode']);
        $this->assertStringContainsString('checksum', strtolower($result['Message']));
    }

    public function test_vnpay_ipn_returns_error_when_order_not_found(): void
    {
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK999_20231029120000')
            ->andReturn(null);

        $params = [
            'vnp_TxnRef' => 'BK999_20231029120000',
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayIpn($params);

        $this->assertEquals('01', $result['RspCode']);
        $this->assertStringContainsString('not found', strtolower($result['Message']));
    }

    public function test_vnpay_ipn_returns_error_for_invalid_merchant(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->amount = 500000;

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_TmnCode' => 'WRONG_TMN',
            'vnp_Amount' => '50000000',
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayIpn($params);

        $this->assertEquals('03', $result['RspCode']);
        $this->assertStringContainsString('merchant', strtolower($result['Message']));
    }

    public function test_vnpay_ipn_returns_error_for_invalid_amount(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->amount = 500000;

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_TmnCode' => 'TEST_TMN',
            'vnp_Amount' => '99999999',
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayIpn($params);

        $this->assertEquals('04', $result['RspCode']);
        $this->assertStringContainsString('amount', strtolower($result['Message']));
    }

    public function test_vnpay_ipn_handles_idempotency(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->amount = 500000;
        $payment->status = 'completed';

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_TmnCode' => 'TEST_TMN',
            'vnp_Amount' => '50000000',
            'vnp_ResponseCode' => '00',
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayIpn($params);

        $this->assertEquals('00', $result['RspCode']);
        $this->assertStringContainsString('success', strtolower($result['Message']));
    }

    public function test_vnpay_ipn_processes_failed_payment(): void
    {
        Config::set('vnpay.tmn_code', 'TEST_TMN');
        Config::set('vnpay.hash_secret', 'TEST_SECRET_KEY');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->id = 1;
        $payment->amount = 500000;
        $payment->status = 'pending';

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $this->paymentRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['status' => 'failed']);

        $params = [
            'vnp_TxnRef' => 'BK123_20231029120000',
            'vnp_Amount' => '50000000',
            'vnp_ResponseCode' => '24',
            'vnp_TmnCode' => 'TEST_TMN',
        ];

        $hash = $this->generateVnpHash($params);
        $params['vnp_SecureHash'] = $hash;

        $result = $this->paymentService->vnpayIpn($params);

        $this->assertEquals('00', $result['RspCode']);
    }

    // ========== vnpayRefund() Tests ==========

    public function test_vnpay_refund_successfully_refunds_payment(): void
    {
        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->id = 1;
        $payment->status = 'completed';

        $refundedPayment = Mockery::mock(Payment::class)->makePartial();
        $refundedPayment->status = 'refunded';

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $this->paymentRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['status' => 'refunded']);

        $this->paymentRepository
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($refundedPayment);

        $result = $this->paymentService->vnpayRefund(
            'BK123_20231029120000',
            500000,
            'Customer request',
            null,
            null
        );

        $this->assertTrue($result['success']);
        $this->assertSame($refundedPayment, $result['payment']);
        $this->assertArrayHasKey('gateway_response', $result);
        $this->assertEquals('00', $result['gateway_response']['vnp_ResponseCode']);
    }

    public function test_vnpay_refund_throws_exception_when_payment_not_found(): void
    {
        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK999_20231029120000')
            ->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        $this->paymentService->vnpayRefund(
            'BK999_20231029120000',
            500000,
            'Test refund',
            null,
            null
        );
    }

    // ========== vnpayQuery() Tests ==========

    public function test_vnpay_query_returns_completed_payment_status(): void
    {
        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->status = 'completed';

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $result = $this->paymentService->vnpayQuery('BK123_20231029120000', null, null);

        $this->assertTrue($result['success']);
        $this->assertSame($payment, $result['payment']);
        $this->assertEquals('00', $result['gateway_response']['vnp_ResponseCode']);
        $this->assertEquals('00', $result['gateway_response']['vnp_TransactionStatus']);
    }

    public function test_vnpay_query_returns_pending_payment_status(): void
    {
        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->status = 'pending';

        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK123_20231029120000')
            ->andReturn($payment);

        $result = $this->paymentService->vnpayQuery('BK123_20231029120000', null, null);

        $this->assertTrue($result['success']);
        $this->assertSame($payment, $result['payment']);
        $this->assertEquals('02', $result['gateway_response']['vnp_ResponseCode']);
        $this->assertEquals('01', $result['gateway_response']['vnp_TransactionStatus']);
    }

    public function test_vnpay_query_throws_exception_when_payment_not_found(): void
    {
        $this->paymentRepository
            ->shouldReceive('findByTransactionId')
            ->once()
            ->with('BK999_20231029120000')
            ->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        $this->paymentService->vnpayQuery('BK999_20231029120000', null, null);
    }

    // ========== Helper Methods ==========

    /**
     * Generate VNPay hash for testing
     */
    private function generateVnpHash(array $params): string
    {
        unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
        ksort($params);
        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = $key . '=' . rawurlencode((string)$value);
        }
        $data = implode('&', $pairs);
        return hash_hmac('sha512', $data, (string)config('vnpay.hash_secret'));
    }
}
