<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get payments with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters = []): LengthAwarePaginator;

    /**
     * Find payment by transaction ID.
     *
     * @param string $transactionId
     * @return Payment|null
     */
    public function findByTransactionId(string $transactionId): ?Payment;

    /**
     * Get payments by booking ID.
     *
     * @param int $bookingId
     * @return mixed
     */
    public function getByBookingId(int $bookingId);

    /**
     * Get payments by user ID.
     *
     * @param int $userId
     * @return mixed
     */
    public function getByUserId(int $userId);
}
