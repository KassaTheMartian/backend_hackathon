<?php

namespace App\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BranchServiceInterface
{
    public function list(Request $request): LengthAwarePaginator;

    public function find(int $id): ?Model;

    public function getAvailableSlots(int $branchId, string $date, int $serviceId, ?int $staffId = null): array;

    public function getNearbyBranches(float $latitude, float $longitude, float $radiusKm = 10): \Illuminate\Support\Collection;
}