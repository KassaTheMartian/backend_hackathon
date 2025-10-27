<?php

namespace App\Services;

use App\Data\Service\ServiceData;
use App\Data\Service\UpdateServiceData;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Services\Contracts\ServiceServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ServiceService implements ServiceServiceInterface
{
    /**
     * Create a new ServiceService instance.
     *
     * @param ServiceRepositoryInterface $services The service repository
     */
    public function __construct(private readonly ServiceRepositoryInterface $services)
    {
    }

    /**
     * Get a paginated list of services.
     *
     * @param Request $request The HTTP request
     * @return LengthAwarePaginator The paginated services
     */
    public function list(Request $request): LengthAwarePaginator
    {
        return $this->services->paginateWithFilters($request);
    }

    /**
     * Create a new service.
     *
     * @param ServiceData $data The service data
     * @return Model The created service
     */
    public function create(ServiceData $data): Model
    {
        $payload = $data->toArray();
        
        // Set default values
        if (!array_key_exists('is_active', $payload)) {
            $payload['is_active'] = true;
        }
        
        return $this->services->create($payload);
    }

    /**
     * Find a service by ID.
     *
     * @param int $id The service ID
     * @return Model|null The service if found, null otherwise
     */
    public function find(int $id): ?Model
    {
        return $this->services->find($id);
    }

    /**
     * Update a service.
     *
     * @param int $id The service ID
     * @param UpdateServiceData $data The service data
     * @return Model|null The updated service if found, null otherwise
     */
    public function update(int $id, UpdateServiceData $data): ?Model
    {
        return $this->services->update($id, $data->toArray());
    }

    /**
     * Delete a service.
     *
     * @param int $id The service ID
     * @return bool True if deleted, false otherwise
     */
    public function delete(int $id): bool
    {
        return $this->services->delete($id);
    }

    /**
     * Get service categories.
     *
     * @param string $locale The locale
     * @return array The categories
     */
    public function categories(string $locale = 'vi'): array
    {
        return $this->services->getCategories($locale)->toArray();
    }
}