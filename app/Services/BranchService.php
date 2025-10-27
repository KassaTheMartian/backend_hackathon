<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class BranchService
{
    /**
     * Get branches with filters.
     */
    public function getBranches(array $filters = []): Collection
    {
        $query = Branch::with(['services', 'staff'])
            ->active()
            ->ordered();

        // Apply filters
        if (isset($filters['latitude']) && isset($filters['longitude'])) {
            // Sort by distance
            $query->get()->sortBy(function ($branch) use ($filters) {
                return $branch->distanceFrom($filters['latitude'], $filters['longitude']);
            });
        }

        return $query->get();
    }

    /**
     * Get branch with details.
     */
    public function getBranchWithDetails(Branch $branch, string $locale = 'vi'): Branch
    {
        return $branch->load([
            'services' => function ($query) {
                $query->active();
            },
            'staff' => function ($query) {
                $query->active();
            }
        ]);
    }

    /**
     * Get available time slots for a branch.
     */
    public function getAvailableSlots(Branch $branch, string $date, int $serviceId, ?int $staffId = null): array
    {
        $service = Service::findOrFail($serviceId);
        $bookingDate = Carbon::parse($date);
        
        // Get existing bookings for the date
        $existingBookings = Booking::where('branch_id', $branch->id)
            ->where('booking_date', $bookingDate)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'no_show')
            ->when($staffId, function ($query) use ($staffId) {
                $query->where('staff_id', $staffId);
            })
            ->get()
            ->pluck('booking_time')
            ->map(function ($time) {
                return Carbon::parse($time)->format('H:i');
            })
            ->toArray();

        // Generate time slots (9:00 AM to 6:00 PM, 30-minute intervals)
        $slots = [];
        $startTime = $bookingDate->copy()->setTime(9, 0);
        $endTime = $bookingDate->copy()->setTime(18, 0);

        while ($startTime->lt($endTime)) {
            $timeString = $startTime->format('H:i');
            $isAvailable = !in_array($timeString, $existingBookings);
            
            $slot = [
                'time' => $timeString,
                'available' => $isAvailable,
            ];

            if ($isAvailable) {
                // Get available staff for this time slot
                $availableStaff = $this->getAvailableStaff($branch, $serviceId, $startTime);
                $slot['staff'] = $availableStaff;
            } else {
                $slot['reason'] = 'Fully booked';
            }

            $slots[] = $slot;
            $startTime->addMinutes(30);
        }

        return $slots;
    }

    /**
     * Get available staff for a specific time slot.
     */
    private function getAvailableStaff(Branch $branch, int $serviceId, Carbon $timeSlot): array
    {
        return $branch->staff()
            ->active()
            ->whereHas('services', function ($query) use ($serviceId) {
                $query->where('services.id', $serviceId);
            })
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'avatar' => $staff->avatar,
                    'rating' => $staff->rating,
                    'years_of_experience' => $staff->years_of_experience,
                ];
            })
            ->toArray();
    }

    /**
     * Get branches near coordinates.
     */
    public function getNearbyBranches(float $latitude, float $longitude, float $radiusKm = 10): Collection
    {
        return Branch::active()
            ->get()
            ->filter(function ($branch) use ($latitude, $longitude, $radiusKm) {
                return $branch->distanceFrom($latitude, $longitude) <= $radiusKm;
            })
            ->sortBy(function ($branch) use ($latitude, $longitude) {
                return $branch->distanceFrom($latitude, $longitude);
            })
            ->values();
    }
}
