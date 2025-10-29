<?php

namespace App\Repositories\Eloquent;

use App\Models\Branch;
use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Class BranchRepository
 */
class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{
    /**
     * Create a new repository instance.
     *
     * @param Branch $model
     */
    public function __construct(Branch $model)
    {
        parent::__construct($model);
    }

    /**
     * Paginate with filters.
     *
     * @param Request $request
     * @return mixed
     */
    public function paginateWithFilters(Request $request)
    {
        return $this->paginateWithRequest($request, sortable: ['id', 'name', 'created_at'], filterable: ['is_active']);
    }

    /**
     * Get allowed includes for eager loading.
     *
     * @return array
     */
    protected function allowedIncludes(): array
    {
        // Whitelist relations that can be eager loaded via ?include=rel1,rel2
        return [
            'staff', 'services', 'bookings'
        ];
    }

    /**
     * Get all active branches.
     */
    public function getActive(): Collection
    {
        return $this->model->active()->ordered()->get();
    }

    /**
     * Get branches near coordinates.
     */
    public function getNearby(float $latitude, float $longitude, float $radiusKm): Collection
    {
        return $this->model
            ->active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->filter(function ($branch) use ($latitude, $longitude, $radiusKm) {
                return $branch->distanceFrom($latitude, $longitude) <= $radiusKm;
            })
            ->sortBy(function ($branch) use ($latitude, $longitude) {
                return $branch->distanceFrom($latitude, $longitude);
            })
            ->values();
    }

    /**
     * Get available staff for a time slot.
     */
    public function getAvailableStaff(int $branchId, int $serviceId, \Carbon\Carbon $timeSlot): array
    {
        return $this->model
            ->find($branchId)
            ->staff()
            ->active()
            ->whereHas('services', function ($query) use ($serviceId) {
                $query->where('services.id', $serviceId);
            })
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'avatar' => $staff->avatar,
                    'rating' => $staff->rating,
                    'years_of_experience' => $staff->years_of_experience,
                ];
            })
            ->toArray();
    }
}

