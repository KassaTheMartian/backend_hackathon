<?php

namespace App\Services;

use App\Models\ServiceCategory;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ServiceCategoryService
{
    public function __construct(
        private ServiceCategoryRepositoryInterface $serviceCategoryRepository
    ) {}

    /**
     * Get all active service categories.
     */
    public function getActiveCategories(): Collection
    {
        return $this->serviceCategoryRepository->getActive();
    }

    /**
     * Get service category by slug.
     */
    public function getCategoryBySlug(string $slug): ?ServiceCategory
    {
        return $this->serviceCategoryRepository->getBySlug($slug);
    }

    /**
     * Create a new service category.
     */
    public function createCategory(array $data): ServiceCategory
    {
        return $this->serviceCategoryRepository->create($data);
    }

    /**
     * Update a service category.
     */
    public function updateCategory(int $id, array $data): ?ServiceCategory
    {
        return $this->serviceCategoryRepository->update($id, $data);
    }

    /**
     * Delete a service category.
     */
    public function deleteCategory(int $id): bool
    {
        return $this->serviceCategoryRepository->delete($id);
    }

    /**
     * Get categories with services count.
     */
    public function getCategoriesWithServicesCount(): Collection
    {
        return $this->serviceCategoryRepository->getWithServicesCount();
    }

    /**
     * Reorder categories.
     */
    public function reorderCategories(array $categoryIds): void
    {
        foreach ($categoryIds as $index => $categoryId) {
            $this->serviceCategoryRepository->updateDisplayOrder($categoryId, $index + 1);
        }
    }
}
