<?php

namespace App\Services;

use App\Data\Demo\DemoData;
use App\Repositories\Contracts\DemoRepositoryInterface;
use App\Services\Contracts\DemoServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Service for handling demo operations.
 *
 * Manages demo listings, creation, updates, and deletions with access control.
 */
class DemoService implements DemoServiceInterface
{
    /**
     * Create a new DemoService instance.
     *
     * @param DemoRepositoryInterface $demos The demo repository
     */
    public function __construct(private readonly DemoRepositoryInterface $demos)
    {
    }

    /**
     * Get a paginated list of demos.
     * - Admin: Can view all demos
     * - User: Can view their own demos
     * - Guest: Can view only active demos (is_active = 1)
     *
     * @param Request $request The HTTP request
     * @return LengthAwarePaginator The paginated demos
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $user = auth()->user();
        
        // Admin can view all demos
        if ($user && $user->is_admin) {
            return $this->demos->paginateWithFilters($request);
        }
        
        // User can view their own demos
        if ($user) {
            $request->merge(['user_id' => $user->id]);
            return $this->demos->paginateWithFilters($request);
        }
        
        // Guest can view only active demos
        $request->merge(['is_active' => true]);
        return $this->demos->paginateWithFilters($request);
    }

    /**
     * Create a new demo.
     *
     * @param DemoData $data The demo data
     * @return Model The created demo
     */
    public function create(DemoData $data): Model
    {
        $payload = $data->toArray();
        if (!array_key_exists('is_active', $payload)) {
            $payload['is_active'] = true;
        }
        
        // Automatically assign the current user as the owner
        if (auth()->check()) {
            $payload['user_id'] = auth()->id();
        }
        
        return $this->demos->create($payload);
    }

    /**
     * Find a demo by ID.
     *
     * @param int $id The demo ID
     * @return Model|null The demo if found, null otherwise
     */
    public function find(int $id): ?Model
    {
        return $this->demos->find($id);
    }

    /**
     * Update a demo.
     *
     * @param int $id The demo ID
     * @param DemoData $data The demo data
     * @return Model|null The updated demo if found, null otherwise
     */
    public function update(int $id, DemoData $data): ?Model
    {
        return $this->demos->update($id, $data->toArray());
    }

    /**
     * Delete a demo.
     *
     * @param int $id The demo ID
     * @return bool True if deleted, false otherwise
     */
    public function delete(int $id): bool
    {
        return $this->demos->delete($id);
    }
}


