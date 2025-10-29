<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PaymentRepository
 */
class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    /**
     * Create a new repository instance.
     *
     * @param Payment $model
     */
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get payments with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters = []): LengthAwarePaginator
    {
        $query = $this->query();

        // Filter by user
        if (isset($filters['user_id'])) {
            $query->whereHas('booking', function ($q) use ($filters) {
                $q->where('user_id', $filters['user_id']);
            });
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by payment method
        if (isset($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        // Filter by booking ID
        if (isset($filters['booking_id'])) {
            $query->where('booking_id', $filters['booking_id']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Find payment by transaction ID.
     *
     * @param string $transactionId
     * @return Payment|null
     */
    public function findByTransactionId(string $transactionId): ?Payment
    {
        return $this->query()
            ->where('transaction_id', $transactionId)
            ->first();
    }

    /**
     * Get payments by booking ID.
     *
     * @param int $bookingId
     * @return Collection
     */
    public function getByBookingId(int $bookingId)
    {
        return $this->query()
            ->where('booking_id', $bookingId)
            ->get();
    }

    /**
     * Get payments by user ID.
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId)
    {
        return $this->query()
            ->whereHas('booking', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->get();
    }
}
