<?php

namespace App\Services;

use App\Data\Booking\BookingData;
use App\Data\Booking\UpdateBookingData;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Services\Contracts\BookingServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingService implements BookingServiceInterface
{
    /**
     * Create a new BookingService instance.
     *
     * @param BookingRepositoryInterface $bookings The booking repository
     */
    public function __construct(private readonly BookingRepositoryInterface $bookings)
    {
    }

    /**
     * Get a paginated list of bookings.
     * - Admin: Can view all bookings
     * - User: Can view their own bookings
     *
     * @param Request $request The HTTP request
     * @return LengthAwarePaginator The paginated bookings
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $user = Auth::user();
        
        // Admin can view all bookings
        if ($user && $user->is_admin) {
            return $this->bookings->paginateWithFilters($request);
        }
        
        // User can view their own bookings
        if ($user) {
            $request->merge(['user_id' => $user->id]);
            return $this->bookings->paginateWithFilters($request);
        }
        
        return $this->bookings->paginateWithFilters($request);
    }

    /**
     * Create a new booking.
     *
     * @param BookingData $data The booking data
     * @return Model The created booking
     */
    public function create(BookingData $data): Model
    {
        $payload = $data->toArray();
        
        // Handle authenticated user vs guest
        if (Auth::check()) {
            // Authenticated user - use current user info
            $payload['user_id'] = Auth::id();
            // Clear guest fields if provided
            unset($payload['guest_name'], $payload['guest_email'], $payload['guest_phone']);
        } else {
            // Guest user - require guest info (already validated in Request)
            $payload['user_id'] = null;
        }
        
        // Get duration and price from service
        $service = \App\Models\Service::find($data->service_id);
        if ($service) {
            $payload['duration'] = $service->duration;
            $payload['service_price'] = $service->price;
            $payload['total_amount'] = $service->price; // Initially same as service price
            $payload['discount_amount'] = 0;
        }
        
        // Set default status
        $payload['status'] = 'pending';
        $payload['payment_status'] = 'pending';
        
        return $this->bookings->create($payload);
    }

    /**
     * Find a booking by ID.
     *
     * @param int $id The booking ID
     * @return Model|null The booking if found, null otherwise
     */
    public function find(int $id): ?Model
    {
        return $this->bookings->find($id);
    }

    /**
     * Update a booking.
     *
     * @param int $id The booking ID
     * @param UpdateBookingData $data The booking data
     * @return Model|null The updated booking if found, null otherwise
     */
    public function update(int $id, UpdateBookingData $data): ?Model
    {
        return $this->bookings->update($id, $data->toArray());
    }

    /**
     * Cancel a booking.
     *
     * @param int $id The booking ID
     * @param string $reason The cancellation reason
     * @return Model|null The cancelled booking if found, null otherwise
     */
    public function cancel(int $id, string $reason): ?Model
    {
        $booking = $this->bookings->find($id);
        if (!$booking) {
            return null;
        }
        
        return $this->bookings->cancel($booking, $reason);
    }

    /**
     * Get user's bookings.
     *
     * @param Request $request The HTTP request
     * @return LengthAwarePaginator The paginated user's bookings
     */
    public function myBookings(Request $request): LengthAwarePaginator
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('User not authenticated');
        }
        $request->merge(['user_id' => $user->id]);
        return $this->bookings->paginateWithFilters($request);
    }

    /**
     * Check time slot availability.
     */
    public function isTimeSlotAvailable(int $branchId, string $date, string $time, ?int $staffId = null): bool
    {
        return $this->bookings->isTimeSlotAvailable($branchId, $date, $time, $staffId);
    }

    /**
     * Get booking statistics.
     */
    public function getBookingStats(array $filters = []): array
    {
        return $this->bookings->getStats($filters);
    }
}