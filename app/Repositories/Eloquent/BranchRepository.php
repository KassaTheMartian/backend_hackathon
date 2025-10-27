<?php

namespace App\Repositories\Eloquent;

use App\Models\Branch;
use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{
    public function __construct(Branch $model)
    {
        parent::__construct($model);
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
     * Get bookings for a specific date.
     */
    public function getBookingsForDate(int $branchId, string $date, ?int $staffId = null): Collection
    {
        $query = DB::table('bookings')
            ->where('branch_id', $branchId)
            ->where('booking_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress']);
            
        if ($staffId) {
            $query->where('staff_id', $staffId);
        }
        
        return $query->get();
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

