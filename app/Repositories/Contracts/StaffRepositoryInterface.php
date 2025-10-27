<?php

namespace App\Repositories\Contracts;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;

interface StaffRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active staff members.
     */
    public function getActive(): Collection;

    /**
     * Get staff members for a specific branch.
     */
    public function getForBranch(int $branchId): Collection;

    /**
     * Get staff members for a specific service.
     */
    public function getForService(int $serviceId): Collection;

    /**
     * Assign services to staff member.
     */
    public function assignServices(Staff $staff, array $serviceIds): void;

    /**
     * Remove services from staff member.
     */
    public function removeServices(Staff $staff, array $serviceIds): void;

    /**
     * Update staff rating.
     */
    public function updateRating(Staff $staff): void;

    /**
     * Get available staff for booking.
     */
    public function getAvailableForBooking(int $branchId, int $serviceId, string $date, string $time): Collection;
}

