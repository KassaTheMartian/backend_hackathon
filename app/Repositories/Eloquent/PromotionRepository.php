<?php

namespace App\Repositories\Eloquent;

use App\Models\Promotion;
use App\Models\User;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PromotionRepository extends BaseRepository implements PromotionRepositoryInterface
{
    public function __construct(Promotion $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active promotions.
     */
    public function getActive(): Collection
    {
        return $this->model->active()->get();
    }

    /**
     * Get promotion by code.
     */
    public function getByCode(string $code): ?Promotion
    {
        return $this->model->where('code', $code)->first();
    }

    /**
     * Record promotion usage.
     */
    public function recordUsage(Promotion $promotion, User $user, int $bookingId, float $discountAmount): void
    {
        $promotion->usages()->create([
            'user_id' => $user->id,
            'booking_id' => $bookingId,
            'discount_amount' => $discountAmount,
        ]);

        // Update used count
        $promotion->increment('used_count');
    }

    /**
     * Get user's available promotions.
     */
    public function getUserPromotions(User $user): Collection
    {
        return $this->model
            ->active()
            ->where(function ($query) use ($user) {
                $query->where('max_uses_per_user', null)
                      ->orWhereHas('usages', function ($q) use ($user) {
                          $q->where('user_id', $user->id)
                            ->havingRaw('COUNT(*) < max_uses_per_user');
                      });
            })
            ->get();
    }

    /**
     * Get promotion usage statistics.
     */
    public function getUsageStats(Promotion $promotion): array
    {
        $totalUsage = $promotion->usages()->count();
        $uniqueUsers = $promotion->usages()->distinct('user_id')->count();
        $totalDiscount = $promotion->usages()->sum('discount_amount');

        return [
            'total_usage' => $totalUsage,
            'unique_users' => $uniqueUsers,
            'total_discount' => $totalDiscount,
            'remaining_uses' => $promotion->max_uses ? max(0, $promotion->max_uses - $totalUsage) : null,
        ];
    }
}

