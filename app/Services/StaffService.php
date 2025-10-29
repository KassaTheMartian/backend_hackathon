<?php

namespace App\Services;

use App\Models\Staff;
use App\Repositories\Contracts\StaffRepositoryInterface;
use App\Services\Contracts\StaffServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service for handling staff operations.
 *
 * Manages staff members, services assignment, and availability.
 */
class StaffService implements StaffServiceInterface
{
    /**
     * Create a new StaffService instance.
     *
     * @param StaffRepositoryInterface $staffRepository The staff repository dependency.
     */
    public function __construct(
        private StaffRepositoryInterface $staffRepository
    ) {}

    /**
     * Get all active staff members.
     *
     * @return Collection<int, Staff>
     */
    public function getActiveStaff(): Collection
    {
        return $this->staffRepository->getActive();
    }

    /**
     * Get paginated staff list.
     *
     * Supports include, sort, direction, per_page, and simple text filters (e.g. position).
     *
     * @param Request $request The HTTP request containing pagination and filter params.
     * @return LengthAwarePaginator Paginated staff result.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        return $this->staffRepository->paginateWithRequest($request, sortable: ['id', 'rating', 'years_of_experience'], filterable: ['position']);
    }

    /**
     * Get staff members for a specific branch.
     *
     * @param int $branchId The branch ID.
     * @return Collection<int, Staff>
     */
    public function getStaffForBranch(int $branchId): Collection
    {
        return $this->staffRepository->getForBranch($branchId);
    }

    /**
     * Get paginated staff list for a branch.
     *
     * @param Request $request The HTTP request containing pagination and filter params.
     * @param int $branchId The branch ID to filter staff by.
     * @return LengthAwarePaginator Paginated staff result scoped to the branch.
     */
    public function listForBranch(Request $request, int $branchId): LengthAwarePaginator
    {
        return $this->staffRepository->paginateForBranch($request, $branchId, sortable: ['id', 'rating', 'years_of_experience'], filterable: ['position']);
    }

    /**
     * Get staff members for a specific service.
     *
     * @param int $serviceId The service ID.
     * @return Collection<int, Staff>
     */
    public function getStaffForService(int $serviceId): Collection
    {
        return $this->staffRepository->getForService($serviceId);
    }

    /**
     * Get staff member by ID.
     *
     * @param int $id The staff ID.
     * @return Staff|null The staff if found, null otherwise.
     */
    public function getStaffById(int $id): ?Staff
    {
        return $this->staffRepository->getById($id);
    }

    /**
     * Create a new staff member.
     *
     * @param array $data The staff attributes to create.
     * @return Staff The created staff entity.
     */
    public function createStaff(array $data): Staff
    {
        return $this->staffRepository->create($data);
    }

    /**
     * Update a staff member.
     *
     * @param int $id The staff ID to update.
     * @param array $data The attributes to update.
     * @return Staff|null The updated staff or null if not found.
     */
    public function updateStaff(int $id, array $data): ?Staff
    {
        return $this->staffRepository->update($id, $data);
    }

    /**
     * Delete a staff member.
     *
     * @param int $id The staff ID to delete.
     * @return bool True if deleted, false otherwise.
     */
    public function deleteStaff(int $id): bool
    {
        return $this->staffRepository->delete($id);
    }

    /**
     * Assign services to a staff member.
     *
     * @param Staff $staff The staff entity.
     * @param array<int,int> $serviceIds The service IDs to assign.
     * @return void
     */
    public function assignServices(Staff $staff, array $serviceIds): void
    {
        $this->staffRepository->assignServices($staff, $serviceIds);
    }

    /**
     * Remove services from a staff member.
     *
     * @param Staff $staff The staff entity.
     * @param array<int,int> $serviceIds The service IDs to remove.
     * @return void
     */
    public function removeServices(Staff $staff, array $serviceIds): void
    {
        $this->staffRepository->removeServices($staff, $serviceIds);
    }

    /**
     * Update staff rating based on approved reviews.
     *
     * @param Staff $staff The staff entity to update rating for.
     * @return void
     */
    public function updateRating(Staff $staff): void
    {
        $this->staffRepository->updateRating($staff);
    }

    /**
     * Get available staff for booking given date/time.
     *
     * @param int $branchId The branch ID.
     * @param int $serviceId The service ID.
     * @param string $date The booking date (Y-m-d).
     * @param string $time The booking time (H:i:s or H:i).
     * @return Collection<int, Staff>
     */
    public function getAvailableStaff(int $branchId, int $serviceId, string $date, string $time): Collection
    {
        return $this->staffRepository->getAvailableForBooking($branchId, $serviceId, $date, $time);
    }
}
