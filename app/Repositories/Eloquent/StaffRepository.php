<?php

namespace App\Repositories\Eloquent;

use App\Models\Staff;
use App\Repositories\Contracts\StaffRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * Class StaffRepository
 */
class StaffRepository extends BaseRepository implements StaffRepositoryInterface
{
    /**
     * Create a new repository instance.
     *
     * @param Staff $model
     */
    public function __construct(Staff $model)
    {
        parent::__construct($model);
    }

    /**
     * Whitelist relations for eager loading via include param.
     */
    protected function allowedIncludes(): array
    {
        return ['user', 'branch', 'services'];
    }

    /**
     * Get all active staff members.
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->model->with('user')->active()->get();
    }

    /**
     * Get staff members for a specific branch.
     *
     * @param int $branchId
     * @return Collection
     */
    public function getForBranch(int $branchId): Collection
    {
        return $this->model->with('user')->forBranch($branchId)->active()->get();
    }

    /**
     * Get staff members for a specific service.
     *
     * @param int $serviceId
     * @return Collection
     */
    public function getForService(int $serviceId): Collection
    {
        return $this->model->with('user')->forService($serviceId)->active()->get();
    }

    public function paginateWithRequest(Request $request, array $sortable = [], array $filterable = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('user');

        $includes = $this->resolveIncludes($request);
        if (!empty($includes)) {
            $query->with($includes);
        }

        foreach ($filterable as $field) {
            if ($request->filled($field)) {
                $value = $request->input($field);
                $query->where($field, 'like', "%{$value}%");
            }
        }

        foreach ($request->all() as $key => $value) {
            if (str_starts_with($key, 'is_') && $value !== null) {
                $query->where($key, filter_var($value, FILTER_VALIDATE_BOOL));
            }
        }

        $sort = $request->input('sort');
        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        if ($sort && in_array($sort, $sortable, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderByDesc('id');
        }

        $perPage = (int) $request->input('per_page', 15);
        return $query->paginate($perPage)->appends($request->query());
    }

    public function paginateForBranch(Request $request, int $branchId, array $sortable = [], array $filterable = []): LengthAwarePaginator
    {
        $requestClone = Request::createFrom($request);
        $query = $this->model->newQuery()->with('user')->forBranch($branchId);

        $includes = $this->resolveIncludes($requestClone);
        if (!empty($includes)) {
            $query->with($includes);
        }

        foreach ($filterable as $field) {
            if ($requestClone->filled($field)) {
                $value = $requestClone->input($field);
                $query->where($field, 'like', "%{$value}%");
            }
        }

        foreach ($requestClone->all() as $key => $value) {
            if (str_starts_with($key, 'is_') && $value !== null) {
                $query->where($key, filter_var($value, FILTER_VALIDATE_BOOL));
            }
        }

        $sort = $requestClone->input('sort');
        $direction = strtolower($requestClone->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        if ($sort && in_array($sort, $sortable, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderByDesc('id');
        }

        $perPage = (int) $requestClone->input('per_page', 15);
        return $query->paginate($perPage)->appends($requestClone->query());
    }

    /**
     * Assign services to staff member.
     *
     * @param Staff $staff
     * @param array $serviceIds
     * @return void
     */
    public function assignServices(Staff $staff, array $serviceIds): void
    {
        $staff->services()->sync($serviceIds);
    }

    /**
     * Remove services from staff member.
     *
     * @param Staff $staff
     * @param array $serviceIds
     * @return void
     */
    public function removeServices(Staff $staff, array $serviceIds): void
    {
        $staff->services()->detach($serviceIds);
    }

    /**
     * Get staff member by ID with user relationship.
     *
     * @param int $id
     * @return Staff|null
     */
    public function getById(int $id): ?Staff
    {
        return $this->model->with('user')->find($id);
    }

    /**
     * Update staff rating.
     *
     * @param Staff $staff
     * @return void
     */
    public function updateRating(Staff $staff): void
    {
        $staff->updateRating();
    }

    /**
     * Get available staff for booking.
     *
     * @param int $branchId
     * @param int $serviceId
     * @param string $date
     * @param string $time
     * @return Collection
     */
    public function getAvailableForBooking(int $branchId, int $serviceId, string $date, string $time): Collection
    {
        return $this->model
            ->with('user')
            ->forBranch($branchId)
            ->forService($serviceId)
            ->active()
            ->whereDoesntHave('bookings', function ($query) use ($date, $time) {
                $query->where('booking_date', $date)
                      ->where('booking_time', $time)
                      ->whereIn('status', ['pending', 'confirmed', 'in_progress']);
            })
            ->get();
    }
}

