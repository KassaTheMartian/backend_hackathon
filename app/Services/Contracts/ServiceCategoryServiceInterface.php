<?php

namespace App\Services\Contracts;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Collection;

interface ServiceCategoryServiceInterface
{
    /**
     * Get active categories.
     *
     * @return Collection
     */
    public function getActiveCategories(): Collection;

    /**
     * Get category by slug.
     *
     * @param string $slug The category slug.
     * @return ServiceCategory|null
     */
    public function getCategoryBySlug(string $slug): ?ServiceCategory;

    /**
     * Create a category.
     *
     * @param array $data The category data.
     * @return ServiceCategory
     */
    public function createCategory(array $data): ServiceCategory;

    /**
     * Update a category.
     *
     * @param int $id The category ID.
     * @param array $data The updated category data.
     * @return ServiceCategory|null
     */
    public function updateCategory(int $id, array $data): ?ServiceCategory;

    /**
     * Delete a category.
     *
     * @param int $id The category ID.
     * @return bool
     */
    public function deleteCategory(int $id): bool;

    /**
     * Get categories with services count.
     *
     * @return Collection
     */
    public function getCategoriesWithServicesCount(): Collection;

    /**
     * Reorder categories.
     *
     * @param array $categoryIds The category IDs.
     * @return void
     */
    public function reorderCategories(array $categoryIds): void;
}
