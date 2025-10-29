<?php

namespace App\Repositories\Contracts;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

interface BranchRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Paginate with request filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function paginateWithFilters(\Illuminate\Http\Request $request);

    /**
     * Get all active branches.
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get branches near coordinates.
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $radiusKm
     * @return Collection
     */
    public function getNearby(float $latitude, float $longitude, float $radiusKm): Collection;

    /**
     * Get available staff for a time slot.
     *
     * @param int $branchId
     * @param int $serviceId
     * @param \Carbon\Carbon $timeSlot
     * @return array
     */
    public function getAvailableStaff(int $branchId, int $serviceId, \Carbon\Carbon $timeSlot): array;
}

