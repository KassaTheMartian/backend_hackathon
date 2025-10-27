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
     * Get service with details (relationships loaded).
     */
    public function getServiceWithDetails(Service $service, string $locale = 'vi'): Service
    {
        return $service->load(['category', 'branches']);
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
    public function updateService(int $id, array $data): ?Service
    {
        return $this->serviceRepository->update($id, $data);
    }

    /**
     * Delete a service.
     */
    public function deleteService(int $id): bool
    {
        return $this->serviceRepository->delete($id);
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