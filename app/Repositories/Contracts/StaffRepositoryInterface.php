<?php

namespace App\Repositories\Contracts;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;

interface StaffRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active staff members.
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get staff members for a specific branch.
     *
     * @param int $branchId
     * @return Collection
     */
    public function getForBranch(int $branchId): Collection;

    /**
     * Get staff members for a specific service.
     *
     * @param int $serviceId
     * @return Collection
     */
    public function getForService(int $serviceId): Collection;

    /**
     * Assign services to staff member.
     *
     * @param Staff $staff
     * @param array $serviceIds
     * @return void
     */
    public function assignServices(Staff $staff, array $serviceIds): void;

    /**
     * Remove services from staff member.
     *
     * @param Staff $staff
     * @param array $serviceIds
     * @return void
     */
    public function removeServices(Staff $staff, array $serviceIds): void;

    /**
     * Update staff rating.
     *
     * @param Staff $staff
     * @return void
     */
    public function updateRating(Staff $staff): void;

    /**
     * Get available staff for booking.
     *
     * @param int $branchId
     * @param int $serviceId
     * @param string $date
     * @param string $time
     * @return Collection
     */
    public function getAvailableForBooking(int $branchId, int $serviceId, string $date, string $time): Collection;
}

