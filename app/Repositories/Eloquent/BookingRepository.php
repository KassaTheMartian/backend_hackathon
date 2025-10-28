<?php

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Models\User;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingRepository extends BaseRepository implements BookingRepositoryInterface
{
    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(Request $request)
    {
        return $this->paginateWithRequest($request, sortable: ['id', 'booking_date', 'created_at'], filterable: ['status', 'branch_id', 'service_id', 'user_id']);
    }

    protected function allowedIncludes(): array
    {
        // Whitelist relations that can be eager loaded via ?include=rel1,rel2
        return [
            'user', 'branch', 'service', 'staff', 'payment', 'review'
        ];
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
     * Get bookings for a specific date.
     */
    public function getBookingsForDate(int $branchId, string $date, ?int $staffId = null): Collection
    {
        $query = $this->model
            ->where('branch_id', $branchId)
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress']);
            
        if ($staffId) {
            $query->where('staff_id', $staffId);
        }
        
        return $query->get();
    }

    /**
     * Check time slot availability.
     */
    public function isTimeSlotAvailable(int $branchId, string $date, string $time, ?int $staffId = null): bool
    {
        // Duration-aware overlap detection with robust parsing
        $timeStr = trim((string)$time);
        $newStart = \Carbon\Carbon::parse(str_contains($timeStr, ' ') || str_contains($timeStr, 'T') ? $timeStr : ($date . ' ' . $timeStr));

        $query = $this->model->where('branch_id', $branchId)
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress']);

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        $bookings = $query->get(['booking_time', 'duration']);

        foreach ($bookings as $b) {
            $existTime = trim((string)$b->booking_time);
            $existStart = \Carbon\Carbon::parse(str_contains($existTime, ' ') || str_contains($existTime, 'T') ? $existTime : ($date . ' ' . $existTime));
            $existEnd = $existStart->copy()->addMinutes((int)$b->duration);
            // assume service duration from caller; use 0 to mean no overlap check
            // Here we conservatively assume minimum 5 minutes overlap when same time
            $newEnd = $newStart->copy()->addMinutes( max(5, (int)($b->duration)) );
            if ($newStart < $existEnd && $existStart < $newEnd) {
                return false;
            }
        }

        return true;
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

    /**
     * Get guest bookings by email.
     */
    public function getGuestBookingsByEmail(string $email, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->whereNull('user_id')
            ->where('guest_email', $email)
            ->with(['branch', 'service', 'staff', 'payment'])
            ->latest('id')
            ->paginate($perPage);
    }
}

