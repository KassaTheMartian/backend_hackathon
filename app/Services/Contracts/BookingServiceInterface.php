<?php

namespace App\Services\Contracts;

use App\Data\Booking\BookingData;
use App\Data\Booking\UpdateBookingData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BookingServiceInterface
{
    public function list(Request $request): LengthAwarePaginator;

    public function create(BookingData $data): Model;

    public function find(int $id): ?Model;

    public function update(int $id, UpdateBookingData $data): ?Model;

    public function cancel(int $id, string $reason): ?Model;

    public function myBookings(Request $request): LengthAwarePaginator;

    /**
     * Availability search by day and optional staff.
     */
    public function availableSlots(int $branchId, int $serviceId, string $date, ?int $staffId = null, int $granularity = 15): array;

    /**
     * Reschedule a booking to new time (with availability check).
     */
    public function reschedule(int $id, string $bookingDate, string $bookingTime, ?int $staffId = null): ?Model;
}