<?php

namespace App\Repositories\Contracts;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Collection;

interface ServiceCategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active service categories.
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get service category by slug.
     *
     * @param string $slug
     * @return ServiceCategory|null
     */
    public function getBySlug(string $slug): ?ServiceCategory;

    /**
     * Get categories with services count.
     *
     * @return Collection
     */
    public function getWithServicesCount(): Collection;

    /**
     * Get service categories with locale.
     *
     * @param string $locale
     * @return array
     */
    public function getCategories(string $locale): array;

    /**
     * Update display order.
     *
     * @param int $categoryId
     * @param int $order
     * @return void
     */
    public function updateDisplayOrder(int $categoryId, int $order): void;
}

