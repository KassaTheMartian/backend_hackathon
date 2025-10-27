<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Models\Service;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Get bookings with filters.
     */
    public function getBookings(array $filters = []): LengthAwarePaginator
    {
        $query = Booking::with(['user', 'branch', 'service', 'staff', 'payment'])
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
     * Get booking with details.
     */
    public function getBookingWithDetails(Booking $booking): Booking
    {
        return $booking->load([
            'user',
            'branch',
            'service',
            'staff',
            'payment',
            'reviews',
            'statusHistory'
        ]);
    }

    /**
     * Create a new booking.
     */
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // Get service details
            $service = Service::findOrFail($data['service_id']);
            $branch = Branch::findOrFail($data['branch_id']);
            
            // Calculate pricing
            $servicePrice = $service->final_price;
            $discountAmount = 0;
            
            // Apply promotion if provided
            if (isset($data['promotion_code'])) {
                $promotion = Promotion::where('code', $data['promotion_code'])->first();
                if ($promotion && $promotion->canBeUsedBy($data['user_id'] ? User::find($data['user_id']) : null, $servicePrice)) {
                    $discountAmount = $promotion->calculateDiscount($servicePrice);
                }
            }
            
            $totalAmount = $servicePrice - $discountAmount;
            
            // Create booking
            $booking = Booking::create([
                'user_id' => $data['user_id'] ?? null,
                'guest_name' => $data['guest_name'] ?? null,
                'guest_email' => $data['guest_email'] ?? null,
                'guest_phone' => $data['guest_phone'] ?? null,
                'branch_id' => $data['branch_id'],
                'service_id' => $data['service_id'],
                'staff_id' => $data['staff_id'] ?? null,
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['booking_time'],
                'duration' => $service->duration,
                'service_price' => $servicePrice,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            // Record promotion usage if applicable
            if (isset($promotion) && $discountAmount > 0) {
                $promotion->usages()->create([
                    'user_id' => $data['user_id'],
                    'booking_id' => $booking->id,
                    'discount_amount' => $discountAmount,
                ]);
            }

            // Record status history
            $booking->statusHistory()->create([
                'new_status' => 'pending',
                'notes' => 'Booking created',
            ]);

            // Dispatch jobs for notifications
            // SendBookingConfirmation::dispatch($booking);
            // SendSMSNotification::dispatch($booking);

            return $booking;
        });
    }

    /**
     * Update a booking.
     */
    public function updateBooking(Booking $booking, array $data): Booking
    {
        if (!$booking->canBeModified()) {
            throw new \Exception('Booking cannot be modified');
        }

        $oldStatus = $booking->status;
        
        $booking->update($data);
        
        // Record status change if status changed
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $booking->statusHistory()->create([
                'old_status' => $oldStatus,
                'new_status' => $data['status'],
                'notes' => 'Booking updated',
            ]);
        }

        return $booking->fresh();
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Booking $booking, string $reason): Booking
    {
        if (!$booking->canBeCancelled()) {
            throw new \Exception('Booking cannot be cancelled');
        }

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
    public function getUserBookings(User $user, array $filters = []): LengthAwarePaginator
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
     * Get booking by code (for guest access).
     */
    public function getBookingByCode(string $code): ?Booking
    {
        return Booking::where('booking_code', $code)
            ->with(['branch', 'service', 'staff', 'payment'])
            ->first();
    }

    /**
     * Check time slot availability.
     */
    public function isTimeSlotAvailable(int $branchId, string $date, string $time, ?int $staffId = null): bool
    {
        $query = Booking::where('branch_id', $branchId)
            ->where('booking_date', $date)
            ->where('booking_time', $time)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'no_show');

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        return $query->count() === 0;
    }
}
