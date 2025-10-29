<?php

namespace App\Repositories\Eloquent;

use App\Models\ServiceCategory;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ServiceCategoryRepository
 */
class ServiceCategoryRepository extends BaseRepository implements ServiceCategoryRepositoryInterface
{
    /**
     * Create a new repository instance.
     *
     * @param ServiceCategory $model
     */
    public function __construct(ServiceCategory $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active service categories.
     */
    public function getActive(): Collection
    {
        return $this->model->active()->ordered()->get();
    }

    /**
     * Get service category by slug.
     */
    public function getBySlug(string $slug): ?ServiceCategory
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Get categories with services count.
     */
    public function getWithServicesCount(): Collection
    {
        return $this->model->withCount('services')->active()->ordered()->get();
    }

    /**
     * Get service categories with locale.
     */
    public function getCategories(string $locale): array
    {
        return $this->model->active()
            ->ordered()
            ->withCount(['services' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(function ($category) use ($locale) {
                return [
                    'id' => $category->id,
                    'name' => $category->name[$locale] ?? $category->name['en'] ?? 'Unknown',
                    'slug' => $category->slug,
                    'description' => $category->description[$locale] ?? $category->description['en'] ?? null,
                    'icon' => $category->icon,
                    'services_count' => $category->services_count,
                ];
            })
            ->toArray();
    }

    /**
     * Update display order.
     */
    public function updateDisplayOrder(int $categoryId, int $order): void
    {
        $this->model->where('id', $categoryId)->update(['display_order' => $order]);
    }
}

