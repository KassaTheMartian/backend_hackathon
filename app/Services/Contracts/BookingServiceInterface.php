<?php

namespace App\Services\Contracts;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BookingServiceInterface
{
    public function getBookings(array $filters = []): LengthAwarePaginator;
    public function getBookingById(int $id): ?Booking;
    public function getBookingByCode(string $code): ?Booking;
    public function getBookingWithDetails(Booking $booking): Booking;
    public function createBooking(array $data): Booking;
    public function updateBooking(int $id, array $data): ?Booking;
    public function cancelBooking(int $id, string $reason): ?Booking;
    public function getUserBookings(User $user, array $filters = []): LengthAwarePaginator;
    public function isTimeSlotAvailable(int $branchId, string $date, string $time, ?int $staffId = null): bool;
    public function getBookingStats(array $filters = []): array;
}
