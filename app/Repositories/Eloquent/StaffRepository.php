<?php

namespace App\Repositories\Eloquent;

use App\Models\Staff;
use App\Repositories\Contracts\StaffRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffRepository extends BaseRepository implements StaffRepositoryInterface
{
    public function __construct(Staff $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active staff members.
     */
    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Get staff members for a specific branch.
     */
    public function getForBranch(int $branchId): Collection
    {
        return $this->model->forBranch($branchId)->active()->get();
    }

    /**
     * Get staff members for a specific service.
     */
    public function getForService(int $serviceId): Collection
    {
        return $this->model->forService($serviceId)->active()->get();
    }

    /**
     * Assign services to staff member.
     */
    public function assignServices(Staff $staff, array $serviceIds): void
    {
        $staff->services()->sync($serviceIds);
    }

    /**
     * Remove services from staff member.
     */
    public function removeServices(Staff $staff, array $serviceIds): void
    {
        $staff->services()->detach($serviceIds);
    }

    /**
     * Update staff rating.
     */
    public function updateRating(Staff $staff): void
    {
        $staff->updateRating();
    }

    /**
     * Get available staff for booking.
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

