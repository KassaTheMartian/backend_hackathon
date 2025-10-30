<?php

namespace App\Services\Contracts;

use App\Data\Booking\BookingData;
use App\Data\Booking\UpdateBookingData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BookingServiceInterface
{
    /**
     * List bookings with pagination.
     *
     * @param Request $request The HTTP request.
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator;

    /**
     * Create a new booking.
     *
     * @param BookingData $data The booking data.
     * @return Model
     */
    public function create(BookingData $data): Model;

    /**
     * Create a booking for guest (no authentication required).
     *
     * @param BookingData $data The guest booking data.
     * @return Model
     */
    public function createGuest(BookingData $data): Model;

    /**
     * Find a booking by ID.
     *
     * @param int $id The booking ID.
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * Update a booking.
     *
     * @param int $id The booking ID.
     * @param UpdateBookingData $data The updated booking data.
     * @return Model|null
     */
    public function update(int $id, UpdateBookingData $data): ?Model;

    /**
     * Cancel a booking.
     *
     * @param int $id The booking ID.
     * @param string $reason The cancellation reason.
     * @return Model|null
     */
    public function cancel(int $id, string $reason): ?Model;

    /**
     * Get user's bookings.
     *
     * @param Request $request The HTTP request.
     * @return LengthAwarePaginator
     */
    public function myBookings(Request $request): LengthAwarePaginator;

    /**
     * Availability search by day and optional staff.
     */
    public function availableSlots(int $branchId, int $serviceId, string $date, ?int $staffId = null, int $granularity = 15): array;

    /**
     * Reschedule a booking to new time (with availability check).
     */
    public function reschedule(int $id, string $bookingDate, string $bookingTime, ?int $staffId = null): ?Model;

    /**
     * Send OTP to guest email for booking verification.
     */
    public function sendGuestBookingOtp(string $email): array;

    /**
     * Get guest bookings by email after OTP verification.
     */
    public function guestBookings(string $email, string $otp, int $perPage = 15): LengthAwarePaginator;
}