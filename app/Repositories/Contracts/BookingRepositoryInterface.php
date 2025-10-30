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
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters): LengthAwarePaginator;

    /**
     * Paginate with request filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function paginateWithFilters(\Illuminate\Http\Request $request);

    /**
     * Get booking by code.
     *
     * @param string $code
     * @return Booking|null
     */
    public function getByCode(string $code): ?Booking;

    /**
     * Cancel a booking.
     *
     * @param Booking $booking
     * @param string $reason
     * @return Booking
     */
    public function cancel(Booking $booking, string $reason): Booking;

    /**
     * Get user's bookings.
     *
     * @param User $user
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getUserBookings(User $user, array $filters): LengthAwarePaginator;

    /**
     * Get bookings for a specific date.
     *
     * @param int $branchId
     * @param string $date
     * @param int|null $staffId
     * @return Collection
     */
    public function getBookingsForDate(int $branchId, string $date, ?int $staffId = null): Collection;

    /**
     * Check time slot availability.
     *
     * @param int $branchId
     * @param string $date
     * @param string $time
     * @param int|null $staffId
     * @return bool
     */
    public function isTimeSlotAvailable(int $branchId, string $date, string $time, ?int $staffId = null): bool;

    /**
     * Get booking statistics.
     *
     * @param array $filters
     * @return array
     */
    public function getStats(array $filters): array;

    /**
     * Get guest bookings by email.
     *
     * @param string $email
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getGuestBookingsByEmail(string $email, int $perPage = 15): LengthAwarePaginator;
}

