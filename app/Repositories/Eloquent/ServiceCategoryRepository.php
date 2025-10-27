<?php

namespace App\Repositories\Eloquent;

use App\Models\ServiceCategory;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ServiceCategoryRepository extends BaseRepository implements ServiceCategoryRepositoryInterface
{
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
     * Update display order.
     */
    public function updateDisplayOrder(int $categoryId, int $order): void
    {
        $this->model->where('id', $categoryId)->update(['display_order' => $order]);
    }
}

