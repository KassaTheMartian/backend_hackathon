<?php

namespace App\Services;

use App\Data\Branch\BranchData;
use App\Data\Branch\UpdateBranchData;
use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Services\Contracts\BranchServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BranchService implements BranchServiceInterface
{
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
     * Create a new branch.
     *
     * @param BranchData $data The branch data
     * @return Model The created branch
     */
    public function create(BranchData $data): Model
    {
        $payload = $data->toArray();
        
        // Set default values
        if (!array_key_exists('is_active', $payload)) {
            $payload['is_active'] = true;
        }
        
        return $this->branches->create($payload);
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
     * Update a branch.
     *
     * @param int $id The branch ID
     * @param UpdateBranchData $data The branch data
     * @return Model|null The updated branch if found, null otherwise
     */
    public function update(int $id, UpdateBranchData $data): ?Model
    {
        return $this->branches->update($id, $data->toArray());
    }

    /**
     * Delete a branch.
     *
     * @param int $id The branch ID
     * @return bool True if deleted, false otherwise
     */
    public function delete(int $id): bool
    {
        return $this->branches->delete($id);
    }

    /**
     * Get available time slots for a branch.
     *
     * @param int $branchId The branch ID
     * @param string $date The date
     * @param int $serviceId The service ID
     * @param int|null $staffId The staff ID
     * @return array The available slots
     */
    public function getAvailableSlots(int $branchId, string $date, int $serviceId, ?int $staffId = null): array
    {
        $branch = $this->find($branchId);
        
        if (!$branch) {
            throw new \Exception('Branch not found');
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
                $slot['reason'] = 'Fully booked';
            }
            
            $slots[] = $slot;
            $startTime->addMinutes(30);
        }
        
        return $slots;
    }
    /**
     * Get branches near coordinates.
     */
    public function getNearbyBranches(float $latitude, float $longitude, float $radiusKm = 10): Collection
    {
        return $this->branches->getNearby($latitude, $longitude, $radiusKm);
    }
}