<?php

namespace App\Repositories\Contracts;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Collection;

interface ServiceCategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active service categories.
     */
    public function getActive(): Collection;

    /**
     * Get service category by slug.
     */
    public function getBySlug(string $slug): ?ServiceCategory;

    /**
     * Get categories with services count.
     */
    public function getWithServicesCount(): Collection;

    /**
     * Update display order.
     */
    public function updateDisplayOrder(int $categoryId, int $order): void;
}

