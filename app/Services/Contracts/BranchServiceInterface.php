<?php

namespace App\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BranchServiceInterface
{
    /**
     * List branches with pagination.
     *
     * @param Request $request The HTTP request.
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator;

    /**
     * Find a branch by ID.
     *
     * @param int $id The branch ID.
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * Get available slots for a branch.
     *
     * @param int $branchId The branch ID.
     * @param string $date The date.
     * @param int $serviceId The service ID.
     * @param int|null $staffId The staff ID.
     * @return array
     */
    public function getAvailableSlots(int $branchId, string $date, int $serviceId, ?int $staffId = null): array;

    /**
     * Get nearby branches.
     *
     * @param float $latitude The latitude.
     * @param float $longitude The longitude.
     * @param float $radiusKm The radius in kilometers.
     * @return \Illuminate\Support\Collection
     */
    public function getNearbyBranches(float $latitude, float $longitude, float $radiusKm = 10): \Illuminate\Support\Collection;
}