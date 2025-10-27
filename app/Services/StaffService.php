<?php

namespace App\Services;

use App\Models\Staff;
use App\Repositories\Contracts\StaffRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffService
{
    public function __construct(
        private StaffRepositoryInterface $staffRepository
    ) {}

    /**
     * Get all active staff members.
     */
    public function getActiveStaff(): Collection
    {
        return $this->staffRepository->getActive();
    }

    /**
     * Get staff members for a specific branch.
     */
    public function getStaffForBranch(int $branchId): Collection
    {
        return $this->staffRepository->getForBranch($branchId);
    }

    /**
     * Get staff members for a specific service.
     */
    public function getStaffForService(int $serviceId): Collection
    {
        return $this->staffRepository->getForService($serviceId);
    }

    /**
     * Get staff member by ID.
     */
    public function getStaffById(int $id): ?Staff
    {
        return $this->staffRepository->getById($id);
    }

    /**
     * Create a new staff member.
     */
    public function createStaff(array $data): Staff
    {
        return $this->staffRepository->create($data);
    }

    /**
     * Update a staff member.
     */
    public function updateStaff(int $id, array $data): ?Staff
    {
        return $this->staffRepository->update($id, $data);
    }

    /**
     * Delete a staff member.
     */
    public function deleteStaff(int $id): bool
    {
        return $this->staffRepository->delete($id);
    }

    /**
     * Assign services to staff member.
     */
    public function assignServices(Staff $staff, array $serviceIds): void
    {
        $this->staffRepository->assignServices($staff, $serviceIds);
    }

    /**
     * Remove services from staff member.
     */
    public function removeServices(Staff $staff, array $serviceIds): void
    {
        $this->staffRepository->removeServices($staff, $serviceIds);
    }

    /**
     * Update staff rating.
     */
    public function updateRating(Staff $staff): void
    {
        $this->staffRepository->updateRating($staff);
    }

    /**
     * Get available staff for booking.
     */
    public function getAvailableStaff(int $branchId, int $serviceId, string $date, string $time): Collection
    {
        return $this->staffRepository->getAvailableForBooking($branchId, $serviceId, $date, $time);
    }
}
