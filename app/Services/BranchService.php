<?php

namespace App\Services;

use App\Models\Branch;
use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BranchService
{
    public function __construct(
        private BranchRepositoryInterface $branchRepository
    ) {}

    /**
     * Get all active branches.
     */
    public function getBranches(array $filters = []): Collection
    {
        $query = $this->branchRepository->getActive();
        
        // Apply filters
        if (isset($filters['latitude']) && isset($filters['longitude'])) {
            $query = $query->sortBy(function ($branch) use ($filters) {
                return $branch->distanceFrom($filters['latitude'], $filters['longitude']);
            });
        }
        
        return $query;
    }

    /**
     * Get branch by ID.
     */
    public function getBranchById(int $id): ?Branch
    {
        return $this->branchRepository->getById($id);
    }

    /**
     * Create a new branch.
     */
    public function createBranch(array $data): Branch
    {
        return $this->branchRepository->create($data);
    }

    /**
     * Update a branch.
     */
    public function updateBranch(Branch $branch, array $data): Branch
    {
        return $this->branchRepository->updateModel($branch, $data);
    }

    /**
     * Delete a branch.
     */
    public function deleteBranch(Branch $branch): bool
    {
        return $this->branchRepository->deleteModel($branch);
    }

    /**
     * Get available time slots for a branch.
     */
    public function getAvailableSlots(int $branchId, string $date, int $serviceId, ?int $staffId = null): array
    {
        $branch = $this->getBranchById($branchId);
        
        if (!$branch) {
            throw new \Exception('Branch not found');
        }
        
        // Get existing bookings for the date
        $existingBookings = $this->branchRepository->getBookingsForDate($branchId, $date, $staffId);
        
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
                $availableStaff = $this->branchRepository->getAvailableStaff($branchId, $serviceId, $startTime);
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
        return $this->branchRepository->getNearby($latitude, $longitude, $radiusKm);
    }
}