<?php

namespace App\Repositories\Contracts;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface PromotionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active promotions.
     */
    public function getActive(): Collection;

    /**
     * Get promotion by code.
     */
    public function getByCode(string $code): ?Promotion;

    /**
     * Record promotion usage.
     */
    public function recordUsage(Promotion $promotion, User $user, int $bookingId, float $discountAmount): void;

    /**
     * Get user's available promotions.
     */
    public function getUserPromotions(User $user): Collection;

    /**
     * Get promotion usage statistics.
     */
    public function getUsageStats(Promotion $promotion): array;
}

