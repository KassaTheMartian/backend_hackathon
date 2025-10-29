<?php

namespace App\Repositories\Eloquent;

use App\Models\Staff;
use App\Repositories\Contracts\StaffRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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
     * Get all active staff members.
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Get staff members for a specific branch.
     *
     * @param int $branchId
     * @return Collection
     */
    public function getForBranch(int $branchId): Collection
    {
        return $this->model->forBranch($branchId)->active()->get();
    }

    /**
     * Get staff members for a specific service.
     *
     * @param int $serviceId
     * @return Collection
     */
    public function getForService(int $serviceId): Collection
    {
        return $this->model->forService($serviceId)->active()->get();
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

