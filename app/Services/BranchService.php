<?php

namespace App\Services;

use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Services\Contracts\BranchServiceInterface;
use App\Traits\HasLocalization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Service for handling branch operations.
 *
 * Manages branch listings, availability, and location-based queries.
 */
class BranchService implements BranchServiceInterface
{
    use HasLocalization;
    /**
     * Create a new BranchService instance.
     *
     * @param BranchRepositoryInterface $branches The branch repository
     * @param BookingRepositoryInterface $bookings The booking repository
     */
    public function __construct(
        private readonly BranchRepositoryInterface $branches,
        private readonly BookingRepositoryInterface $bookings
    )
    {
    }

    /**
     * Get a paginated list of branches.
     *
     * @param Request $request The HTTP request
     * @return LengthAwarePaginator The paginated branches
     */
    public function list(Request $request): LengthAwarePaginator
    {
        return $this->branches->paginateWithFilters($request);
    }

    /**
     * Find a branch by ID.
     *
     * @param int $id The branch ID
     * @return Model|null The branch if found, null otherwise
     */
    public function find(int $id): ?Model
    {
        return $this->branches->find($id);
    }

    /**
     * Find branch by slug.
     *
     * @param string $slug
     * @return Model|null
     */
    public function findBySlug(string $slug): ?Model
    {
        return $this->branches->findBySlug($slug);
    }

    /**
     * Get available time slots for a branch.
     *
     * @param int $branchId The branch ID
     * @param string $date The date
     * @param int $serviceId The service ID
     * @param int|null $staffId The staff ID
     * @param Request|null $request The HTTP request for localization
     * @return array The available slots
     */
    public function getAvailableSlots(int $branchId, string $date, int $serviceId, ?int $staffId = null, ?Request $request = null): array
    {
        $branch = $this->find($branchId);
        
        if (!$branch) {
            throw new \Exception(__('branches.not_found'));
        }
        
        // Get existing bookings for the date
        $existingBookings = $this->bookings->getBookingsForDate($branchId, $date, $staffId);
        
        // Generate time slots (9:00 AM to 6:00 PM, 30-minute intervals)
        $slots = [];
        $startTime = \Carbon\Carbon::parse($date)->setTime(9, 0);
        $endTime = \Carbon\Carbon::parse($date)->setTime(18, 0);
        
        while ($startTime->lt($endTime)) {
            $timeString = $startTime->format('H:i');
            $isAvailable = !$existingBookings->contains('booking_time', $timeString);
            
            $slot = [
                'time' => $timeString,
                'available' => $isAvailable,
            ];
            
            if ($isAvailable) {
                // Get available staff for this time slot
                $availableStaff = $this->branches->getAvailableStaff($branchId, $serviceId, $startTime);
                $slot['staff'] = $availableStaff;
            } else {
                $slot['reason'] = __('branches.fully_booked');
            }
            
            $slots[] = $slot;
            $startTime->addMinutes(30);
        }
        
        return $slots;
    }
    /**
     * Get branches near coordinates.
     *
     * @param float $latitude The latitude.
     * @param float $longitude The longitude.
     * @param float $radiusKm The radius in kilometers.
     * @return Collection
     */
    public function getNearbyBranches(float $latitude, float $longitude, float $radiusKm = 10): Collection
    {
        return $this->branches->getNearby($latitude, $longitude, $radiusKm);
    }
}