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
     *
     * @param Request $request The HTTP request.
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator;

    // Removed Stripe-related methods from interface

    /**
     * VNPay: Create payment URL and Payment record.
     *
     * @param int $bookingId The booking ID.
     * @param int $amount The payment amount.
     * @param string|null $bankCode The bank code.
     * @param string|null $language The language.
     * @param string|null $guestEmail The guest email.
     * @param string|null $guestPhone The guest phone.
     * @return array
     */
    public function vnpayCreate(int $bookingId, int $amount, ?string $bankCode, ?string $language, ?string $guestEmail, ?string $guestPhone): array;

    /**
     * VNPay: Handle return URL (client redirect back).
     *
     * @param array $params The parameters.
     * @return array
     */
    public function vnpayReturn(array $params): array;

    /**
     * VNPay: Handle IPN notification.
     *
     * @param array $params The parameters.
     * @return array
     */
    public function vnpayIpn(array $params): array;

    /**
     * VNPay: Refund transaction.
     *
     * @param string $transactionId The transaction ID.
     * @param int $amount The amount.
     * @param string $reason The reason.
     * @param string|null $guestEmail The guest email.
     * @param string|null $guestPhone The guest phone.
     * @return array
     */
    public function vnpayRefund(string $transactionId, int $amount, string $reason, ?string $guestEmail, ?string $guestPhone): array;

    /**
     * VNPay: Query transaction status.
     *
     * @param string $transactionId The transaction ID.
     * @param string|null $guestEmail The guest email.
     * @param string|null $guestPhone The guest phone.
     * @return array
     */
    public function vnpayQuery(string $transactionId, ?string $guestEmail, ?string $guestPhone): array;
}
