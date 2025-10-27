<?php

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Models\User;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookingRepository extends BaseRepository implements BookingRepositoryInterface
{
    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }

    /**
     * Get bookings with filters.
     */
    public function getWithFilters(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'branch', 'service', 'staff', 'payment'])
            ->latest();

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('booking_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('booking_date', '<=', $filters['date_to']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get booking by code.
     */
    public function getByCode(string $code): ?Booking
    {
        return $this->model->where('booking_code', $code)
            ->with(['branch', 'service', 'staff', 'payment'])
            ->first();
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Booking $booking, string $reason): Booking
    {
        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        // Record status change
        $booking->statusHistory()->create([
            'old_status' => $booking->getOriginal('status'),
            'new_status' => 'cancelled',
            'notes' => $reason,
        ]);

        return $booking->fresh();
    }

    /**
     * Get user's bookings.
     */
    public function getUserBookings(User $user, array $filters): LengthAwarePaginator
    {
        $query = $user->bookings()
            ->with(['branch', 'service', 'staff', 'payment'])
            ->latest();

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Check time slot availability.
     */
    public function isTimeSlotAvailable(int $branchId, string $date, string $time, ?int $staffId = null): bool
    {
        $query = $this->model->where('branch_id', $branchId)
            ->where('booking_date', $date)
            ->where('booking_time', $time)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress']);

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        return $query->count() === 0;
    }

    /**
     * Get booking statistics.
     */
    public function getStats(array $filters): array
    {
        $query = $this->model->query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('booking_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('booking_date', '<=', $filters['date_to']);
        }

        $totalBookings = $query->count();
        $confirmedBookings = $query->where('status', 'confirmed')->count();
        $cancelledBookings = $query->where('status', 'cancelled')->count();
        $totalRevenue = $query->where('status', 'confirmed')->sum('total_amount');

        return [
            'total_bookings' => $totalBookings,
            'confirmed_bookings' => $confirmedBookings,
            'cancelled_bookings' => $cancelledBookings,
            'total_revenue' => $totalRevenue,
            'cancellation_rate' => $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100, 2) : 0,
        ];
    }
}

