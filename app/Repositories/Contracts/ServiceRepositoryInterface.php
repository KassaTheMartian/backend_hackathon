<?php

namespace App\Repositories\Contracts;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ServiceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get services with filters.
     */
    public function getWithFilters(array $filters): LengthAwarePaginator;

    /**
     * Paginate with request filters.
     */
    public function paginateWithFilters(\Illuminate\Http\Request $request);

    /**
     * Get service by slug.
     */
    public function getBySlug(string $slug): ?Service;

    /**
     * Get featured services.
     */
    public function getFeatured(int $limit): Collection;

    /**
     * Get related services.
     */
    public function getRelated(Service $service, int $limit): Collection;

    /**
     * Increment service views.
     */
    public function incrementViews(Service $service): void;
}

