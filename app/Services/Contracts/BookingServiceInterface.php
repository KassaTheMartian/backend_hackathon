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
}