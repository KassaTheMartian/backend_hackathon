<?php

namespace App\Services\Contracts;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface PromotionServiceInterface
{
    /**
     * Get active promotions.
     *
     * @return Collection
     */
    public function getActivePromotions(): Collection;

    /**
     * Get promotion by code.
     *
     * @param string $code The promotion code.
     * @return Promotion|null
     */
    public function getPromotionByCode(string $code): ?Promotion;

    /**
     * Validate promotion code.
     *
     * @param string $code The promotion code.
     * @param User $user The user.
     * @param float $amount The amount.
     * @return array
     */
    public function validatePromotionCode(string $code, User $user, float $amount = 0): array;

    /**
     * Apply promotion.
     *
     * @param Promotion $promotion The promotion.
     * @param int $bookingId The booking ID.
     * @param User $user The user.
     * @param float $amount The amount.
     * @return float
     */
    public function applyPromotion(Promotion $promotion, int $bookingId, User $user, float $amount): float;

    /**
     * Get user promotions.
     *
     * @param User $user The user.
     * @return Collection
     */
    public function getUserPromotions(User $user): Collection;

    /**
     * Create a promotion.
     *
     * @param array $data The promotion data.
     * @return Promotion
     */
    public function createPromotion(array $data): Promotion;

    /**
     * Update a promotion.
     *
     * @param int $id The promotion ID.
     * @param array $data The updated promotion data.
     * @return Promotion|null
     */
    public function updatePromotion(int $id, array $data): ?Promotion;

    /**
     * Delete a promotion.
     *
     * @param int $id The promotion ID.
     * @return bool
     */
    public function deletePromotion(int $id): bool;

    /**
     * Get promotion stats.
     *
     * @param Promotion $promotion The promotion.
     * @return array
     */
    public function getPromotionStats(Promotion $promotion): array;
}
