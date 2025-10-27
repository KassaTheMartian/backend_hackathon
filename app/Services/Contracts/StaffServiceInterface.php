<?php

namespace App\Services\Contracts;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;

interface StaffServiceInterface
{
    public function getActiveStaff(): Collection;
    public function getStaffForBranch(int $branchId): Collection;
    public function getStaffForService(int $serviceId): Collection;
    public function getStaffById(int $id): ?Staff;
    public function createStaff(array $data): Staff;
    public function updateStaff(int $id, array $data): ?Staff;
    public function deleteStaff(int $id): bool;
    public function assignServices(Staff $staff, array $serviceIds): void;
    public function removeServices(Staff $staff, array $serviceIds): void;
    public function updateRating(Staff $staff): void;
    public function getAvailableStaff(int $branchId, int $serviceId, string $date, string $time): Collection;
}
