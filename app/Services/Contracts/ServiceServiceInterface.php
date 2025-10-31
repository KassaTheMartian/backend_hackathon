<?php

namespace App\Services\Contracts;

use App\Data\Service\ServiceData;
use App\Data\Service\UpdateServiceData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface ServiceServiceInterface
{
    /**
     * List services with pagination.
     *
     * @param Request $request The HTTP request.
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator;

    /**
     * Create a new service.
     *
     * @param ServiceData $data The service data.
     * @return Model
     */
    public function create(ServiceData $data): Model;

    /**
     * Find a service by ID.
     *
     * @param int $id The service ID.
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * Find a service by slug.
     *
     * @param string $slug The service slug.
     * @return Model|null
     */
    public function findBySlug(string $slug): ?Model;

    /**
     * Update a service.
     *
     * @param int $id The service ID.
     * @param UpdateServiceData $data The updated service data.
     * @return Model|null
     */
    public function update(int $id, UpdateServiceData $data): ?Model;

    /**
     * Delete a service.
     *
     * @param int $id The service ID.
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get service categories.
     *
     * @param string $locale The locale.
     * @return array
     */
    public function categories(string $locale = 'vi'): array;
}