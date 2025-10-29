<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BaseRepositoryInterface
{
    /**
     * Get all records.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Paginate records.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Paginate with request filters.
     *
     * @param Request $request
     * @param array $sortable
     * @param array $filterable
     * @return LengthAwarePaginator
     */
    public function paginateWithRequest(Request $request, array $sortable = [], array $filterable = []): LengthAwarePaginator;

    /**
     * Find record by ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * Get record by ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function getById(int $id): ?Model;

    /**
     * Create a new record.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * Update record by ID.
     *
     * @param int $id
     * @param array $attributes
     * @return Model|null
     */
    public function update(int $id, array $attributes): ?Model;

    /**
     * Delete record by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}


