<?php

namespace App\Services;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ServiceService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}

    /**
     * Get services with filters.
     */
    public function getServices(array $filters = []): LengthAwarePaginator
    {
        return $this->serviceRepository->getWithFilters($filters);
    }

    /**
     * Get service by ID.
     */
    public function getServiceById(int $id): ?Service
    {
        return $this->serviceRepository->getById($id);
    }

    /**
     * Get service by slug.
     */
    public function getServiceBySlug(string $slug): ?Service
    {
        return $this->serviceRepository->getBySlug($slug);
    }

    /**
     * Create a new service.
     */
    public function createService(array $data): Service
    {
        return $this->serviceRepository->create($data);
    }

    /**
     * Update a service.
     */
    public function updateService(Service $service, array $data): Service
    {
        return $this->serviceRepository->updateModel($service, $data);
    }

    /**
     * Delete a service.
     */
    public function deleteService(Service $service): bool
    {
        return $this->serviceRepository->deleteModel($service);
    }

    /**
     * Get featured services.
     */
    public function getFeaturedServices(int $limit = 6): Collection
    {
        return $this->serviceRepository->getFeatured($limit);
    }

    /**
     * Get related services.
     */
    public function getRelatedServices(Service $service, int $limit = 4): Collection
    {
        return $this->serviceRepository->getRelated($service, $limit);
    }

    /**
     * Increment service views.
     */
    public function incrementViews(Service $service): void
    {
        $this->serviceRepository->incrementViews($service);
    }

    /**
     * Get service categories.
     */
    public function getCategories(string $locale = 'vi'): Collection
    {
        return $this->serviceRepository->getCategories($locale);
    }
}