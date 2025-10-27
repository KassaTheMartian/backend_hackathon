<?php

namespace App\Services\Contracts;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Collection;

interface ServiceCategoryServiceInterface
{
    public function getActiveCategories(): Collection;
    public function getCategoryBySlug(string $slug): ?ServiceCategory;
    public function createCategory(array $data): ServiceCategory;
    public function updateCategory(int $id, array $data): ?ServiceCategory;
    public function deleteCategory(int $id): bool;
    public function getCategoriesWithServicesCount(): Collection;
    public function reorderCategories(array $categoryIds): void;
}
