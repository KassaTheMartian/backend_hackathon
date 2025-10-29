<?php

namespace App\Repositories\Contracts;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface PromotionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active promotions.
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get promotion by code.
     *
     * @param string $code
     * @return Promotion|null
     */
    public function getByCode(string $code): ?Promotion;

    /**
     * Record promotion usage.
     *
     * @param Promotion $promotion
     * @param User $user
     * @param int $bookingId
     * @param float $discountAmount
     * @return void
     */
    public function recordUsage(Promotion $promotion, User $user, int $bookingId, float $discountAmount): void;

    /**
     * Get user's available promotions.
     *
     * @param User $user
     * @return Collection
     */
    public function getUserPromotions(User $user): Collection;

    /**
     * Get promotion usage statistics.
     *
     * @param Promotion $promotion
     * @return array
     */
    public function getUsageStats(Promotion $promotion): array;
}

