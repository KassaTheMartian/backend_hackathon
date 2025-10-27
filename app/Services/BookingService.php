<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * Get bookings with filters.
     */
    public function getBookings(array $filters = []): LengthAwarePaginator
    {
        return $this->bookingRepository->getWithFilters($filters);
    }

    /**
     * Get booking by ID.
     */
    public function getBookingById(int $id): ?Booking
    {
        return $this->bookingRepository->getById($id);
    }

    /**
     * Get booking by code.
     */
    public function getBookingByCode(string $code): ?Booking
    {
        return $this->bookingRepository->getByCode($code);
    }

    /**
     * Get booking with details (relationships loaded).
     */
    public function getBookingWithDetails(Booking $booking): Booking
    {
        return $booking->load(['service', 'branch', 'staff', 'payment', 'review']);
    }

    /**
     * Create a new booking.
     */
    public function createBooking(array $data): Booking
    {
        return $this->bookingRepository->create($data);
    }

    /**
     * Update a booking.
     */
    public function updateBooking(Booking $booking, array $data): Booking
    {
        return $this->bookingRepository->updateModel($booking, $data);
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Booking $booking, string $reason): Booking
    {
        return $this->bookingRepository->cancel($booking, $reason);
    }

    /**
     * Get user's bookings.
     */
    public function getUserBookings(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->bookingRepository->getUserBookings($user, $filters);
    }

    /**
     * Check time slot availability.
     */
    public function isTimeSlotAvailable(int $branchId, string $date, string $time, ?int $staffId = null): bool
    {
        return $this->bookingRepository->isTimeSlotAvailable($branchId, $date, $time, $staffId);
    }

    /**
     * Get booking statistics.
     */
    public function getBookingStats(array $filters = []): array
    {
        return $this->bookingRepository->getStats($filters);
    }
}