<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get bookings with filters.
     */
    public function getWithFilters(array $filters): LengthAwarePaginator;

    /**
     * Paginate with request filters.
     */
    public function paginateWithFilters(\Illuminate\Http\Request $request);

    /**
     * Get booking by code.
     */
    public function getByCode(string $code): ?Booking;

    /**
     * Cancel a booking.
     */
    public function cancel(Booking $booking, string $reason): Booking;

    /**
     * Get user's bookings.
     */
    public function getUserBookings(User $user, array $filters): LengthAwarePaginator;

    /**
     * Get bookings for a specific date.
     */
    public function getBookingsForDate(int $branchId, string $date, ?int $staffId = null): Collection;

    /**
     * Check time slot availability.
     */
    public function isTimeSlotAvailable(int $branchId, string $date, string $time, ?int $staffId = null): bool;

    /**
     * Get booking statistics.
     */
    public function getStats(array $filters): array;
}

