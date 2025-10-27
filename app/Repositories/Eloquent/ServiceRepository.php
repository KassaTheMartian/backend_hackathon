<?php

namespace App\Repositories\Eloquent;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository extends BaseRepository implements ServiceRepositoryInterface
{
    public function __construct(Service $model)
    {
        parent::__construct($model);
    }

    /**
     * Get services with filters.
     */
    public function getWithFilters(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['category', 'branches', 'reviews'])
            ->active()
            ->ordered();

        // Apply filters
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_featured'])) {
            $query->featured();
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereRaw('JSON_EXTRACT(name, "$.vi") LIKE ?', ["%{$filters['search']}%"])
                  ->orWhereRaw('JSON_EXTRACT(name, "$.en") LIKE ?', ["%{$filters['search']}%"]);
            });
        }

        // Apply sorting
        $sortBy = $filters['sort'] ?? 'display_order';
        $sortOrder = $filters['order'] ?? 'asc';
        
        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } elseif ($sortBy === 'name') {
            $query->orderByRaw("JSON_EXTRACT(name, '$.vi') {$sortOrder}");
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get service by slug.
     */
    public function getBySlug(string $slug): ?Service
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Get featured services.
     */
    public function getFeatured(int $limit): Collection
    {
        return $this->model->with(['category', 'branches'])
            ->active()
            ->featured()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * Get related services.
     */
    public function getRelated(Service $service, int $limit): Collection
    {
        return $this->model->with(['category'])
            ->where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->active()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * Increment service views.
     */
    public function incrementViews(Service $service): void
    {
        $service->increment('views_count');
    }

    /**
     * Get service categories.
     */
    public function getCategories(string $locale): Collection
    {
        return $this->model->getCategories($locale);
    }
}

