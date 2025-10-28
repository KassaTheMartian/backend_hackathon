<?php

namespace App\Services\Contracts;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface PaymentServiceInterface
{
    /**
     * List payments with filters and scoping.
     */
    public function list(Request $request): LengthAwarePaginator;

    // Removed Stripe-related methods from interface

    /**
     * VNPay: Create payment URL and Payment record.
     */
    public function vnpayCreate(int $bookingId, ?string $bankCode, ?string $language, ?string $guestEmail, ?string $guestPhone): array;

    /**
     * VNPay: Handle return URL (client redirect back).
     */
    public function vnpayReturn(array $params): array;

    /**
     * VNPay: Handle IPN notification.
     */
    public function vnpayIpn(array $params): array;

    /**
     * VNPay: Refund transaction.
     */
    public function vnpayRefund(string $transactionId, int $amount, string $reason, ?string $guestEmail, ?string $guestPhone): array;

    /**
     * VNPay: Query transaction status.
     */
    public function vnpayQuery(string $transactionId, ?string $guestEmail, ?string $guestPhone): array;
}
