<?php

namespace App\Services\Contracts;

use App\Models\Staff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface StaffServiceInterface
{
    /**
     * Get active staff.
     *
     * @return Collection
     */
    public function getActiveStaff(): Collection;

    /**
     * Paginated list of staff.
     */
    public function list(Request $request): LengthAwarePaginator;

    /**
     * Get staff for branch.
     *
     * @param int $branchId The branch ID.
     * @return Collection
     */
    public function getStaffForBranch(int $branchId): Collection;

    /**
     * Paginated list of staff for a branch.
     */
    public function listForBranch(Request $request, int $branchId): LengthAwarePaginator;

    /**
     * Get staff for service.
     *
     * @param int $serviceId The service ID.
     * @return Collection
     */
    public function getStaffForService(int $serviceId): Collection;

    /**
     * Get staff by ID.
     *
     * @param int $id The staff ID.
     * @return Staff|null
     */
    public function getStaffById(int $id): ?Staff;

    /**
     * Create a staff.
     *
     * @param array $data The staff data.
     * @return Staff
     */
    public function createStaff(array $data): Staff;

    /**
     * Update a staff.
     *
     * @param int $id The staff ID.
     * @param array $data The updated staff data.
     * @return Staff|null
     */
    public function updateStaff(int $id, array $data): ?Staff;

    /**
     * Delete a staff.
     *
     * @param int $id The staff ID.
     * @return bool
     */
    public function deleteStaff(int $id): bool;

    /**
     * Assign services to staff.
     *
     * @param Staff $staff The staff.
     * @param array $serviceIds The service IDs.
     * @return void
     */
    public function assignServices(Staff $staff, array $serviceIds): void;

    /**
     * Remove services from staff.
     *
     * @param Staff $staff The staff.
     * @param array $serviceIds The service IDs.
     * @return void
     */
    public function removeServices(Staff $staff, array $serviceIds): void;

    /**
     * Update staff rating.
     *
     * @param Staff $staff The staff.
     * @return void
     */
    public function updateRating(Staff $staff): void;

    /**
     * Get available staff.
     *
     * @param int $branchId The branch ID.
     * @param int $serviceId The service ID.
     * @param string $date The date.
     * @param string $time The time.
     * @return Collection
     */
    public function getAvailableStaff(int $branchId, int $serviceId, string $date, string $time): Collection;
}
