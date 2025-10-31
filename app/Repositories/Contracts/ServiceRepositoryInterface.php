<?php

namespace App\Repositories\Contracts;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ServiceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get services with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters): LengthAwarePaginator;

    /**
     * Paginate with request filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function paginateWithFilters(\Illuminate\Http\Request $request);

    /**
     * Get service by slug.
     *
     * @param string $slug
     * @return Service|null
     */
    public function getBySlug(string $slug): ?Service;

    /**
     * Find service by slug.
     *
     * @param string $slug
     * @return Service|null
     */
    public function findBySlug(string $slug): ?Service;

    /**
     * Get featured services.
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeatured(int $limit): Collection;

    /**
     * Get related services.
     *
     * @param Service $service
     * @param int $limit
     * @return Collection
     */
    public function getRelated(Service $service, int $limit): Collection;

    /**
     * Increment service views.
     *
     * @param Service $service
     * @return void
     */
    public function incrementViews(Service $service): void;
}

