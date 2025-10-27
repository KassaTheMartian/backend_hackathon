<?php

namespace App\Services\Contracts;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

interface BranchServiceInterface
{
    public function getBranches(array $filters = []): Collection;
    public function getBranchById(int $id): ?Branch;
    public function createBranch(array $data): Branch;
    public function updateBranch(int $id, array $data): ?Branch;
    public function deleteBranch(int $id): bool;
    public function getAvailableSlots(int $branchId, string $date, int $serviceId, ?int $staffId = null): array;
    public function getNearbyBranches(float $latitude, float $longitude, float $radiusKm = 10): Collection;
}
