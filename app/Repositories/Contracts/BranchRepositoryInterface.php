<?php

namespace App\Repositories\Contracts;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

interface BranchRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Paginate with request filters.
     */
    public function paginateWithFilters(\Illuminate\Http\Request $request);

    /**
     * Get all active branches.
     */
    public function getActive(): Collection;

    /**
     * Get branches near coordinates.
     */
    public function getNearby(float $latitude, float $longitude, float $radiusKm): Collection;

    /**
     * Get available staff for a time slot.
     */
    public function getAvailableStaff(int $branchId, int $serviceId, \Carbon\Carbon $timeSlot): array;
}

