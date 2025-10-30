<?php

namespace App\Services\Contracts;

use App\Data\Demo\DemoData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface DemoServiceInterface
{
    /**
     * List demos with pagination.
     *
     * @param Request $request The HTTP request.
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator;

    /**
     * Create a new demo.
     *
     * @param DemoData $data The demo data.
     * @return Model
     */
    public function create(DemoData $data): Model;

    /**
     * Find a demo by ID.
     *
     * @param int $id The demo ID.
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * Update a demo.
     *
     * @param int $id The demo ID.
     * @param DemoData $data The updated demo data.
     * @return Model|null
     */
    public function update(int $id, DemoData $data): ?Model;

    /**
     * Delete a demo.
     *
     * @param int $id The demo ID.
     * @return bool
     */
    public function delete(int $id): bool;
}


