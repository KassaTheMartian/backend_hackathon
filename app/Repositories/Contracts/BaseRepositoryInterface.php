<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BaseRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function paginateWithRequest(Request $request, array $sortable = [], array $filterable = []): LengthAwarePaginator;

    public function find(int $id): ?Model;

    public function getById(int $id): ?Model;

    public function create(array $attributes): Model;

    public function update(int $id, array $attributes): ?Model;

    public function delete(int $id): bool;
}


