<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ServiceService
{
    /**
     * Get services with filters.
     */
    public function getServices(array $filters = []): LengthAwarePaginator
    {
        $query = Service::with(['category', 'branches', 'reviews'])
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
     * Get service with details.
     */
    public function getServiceWithDetails(Service $service, string $locale = 'vi'): Service
    {
        return $service->load([
            'category',
            'branches' => function ($query) {
                $query->active();
            },
            'staff' => function ($query) {
                $query->active();
            },
            'reviews' => function ($query) {
                $query->approved()->latest();
            }
        ]);
    }

    /**
     * Create a new service.
     */
    public function createService(array $data): Service
    {
        return Service::create($data);
    }

    /**
     * Update a service.
     */
    public function updateService(Service $service, array $data): Service
    {
        $service->update($data);
        return $service->fresh();
    }

    /**
     * Delete a service.
     */
    public function deleteService(Service $service): void
    {
        $service->delete();
    }

    /**
     * Get service categories.
     */
    public function getCategories(string $locale = 'vi'): Collection
    {
        return ServiceCategory::active()
            ->ordered()
            ->get()
            ->map(function ($category) use ($locale) {
                return [
                    'id' => $category->id,
                    'name' => $category->name[$locale] ?? $category->name['vi'] ?? '',
                    'slug' => $category->slug,
                    'description' => $category->description[$locale] ?? $category->description['vi'] ?? '',
                    'icon' => $category->icon,
                    'services_count' => $category->services()->active()->count(),
                ];
            });
    }

    /**
     * Get featured services.
     */
    public function getFeaturedServices(int $limit = 6): Collection
    {
        return Service::with(['category', 'branches'])
            ->active()
            ->featured()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * Get related services.
     */
    public function getRelatedServices(Service $service, int $limit = 4): Collection
    {
        return Service::with(['category'])
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
}
