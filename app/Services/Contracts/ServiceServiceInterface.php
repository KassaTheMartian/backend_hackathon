<?php

namespace App\Services\Contracts;

use App\Data\Service\ServiceData;
use App\Data\Service\UpdateServiceData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface ServiceServiceInterface
{
    public function list(Request $request): LengthAwarePaginator;

    public function create(ServiceData $data): Model;

    public function find(int $id): ?Model;

    public function update(int $id, UpdateServiceData $data): ?Model;

    public function delete(int $id): bool;

    public function categories(string $locale = 'vi'): array;
}