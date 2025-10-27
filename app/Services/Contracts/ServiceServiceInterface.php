<?php

namespace App\Services\Contracts;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ServiceServiceInterface
{
    public function getServices(array $filters = []): LengthAwarePaginator;
    public function getServiceById(int $id): ?Service;
    public function getServiceBySlug(string $slug): ?Service;
    public function createService(array $data): Service;
    public function updateService(int $id, array $data): ?Service;
    public function deleteService(int $id): bool;
    public function getFeaturedServices(int $limit = 6): Collection;
    public function getRelatedServices(Service $service, int $limit = 4): Collection;
    public function incrementViews(Service $service): void;
    public function getCategories(string $locale = 'vi'): Collection;
}
