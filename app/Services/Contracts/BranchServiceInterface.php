<?php

namespace App\Services\Contracts;

use App\Data\Branch\BranchData;
use App\Data\Branch\UpdateBranchData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BranchServiceInterface
{
    public function list(Request $request): LengthAwarePaginator;

    public function create(BranchData $data): Model;

    public function find(int $id): ?Model;

    public function update(int $id, UpdateBranchData $data): ?Model;

    public function delete(int $id): bool;

    public function getAvailableSlots(int $branchId, string $date, int $serviceId, ?int $staffId = null): array;
}