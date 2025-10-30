<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Promotion;
use App\Models\User;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service for handling promotion operations.
 *
 * Manages promotions, discounts, and coupon validation.
 */
class PromotionService
{
    public function __construct(
        private PromotionRepositoryInterface $promotionRepository
    ) {}

    /**
     * Get all active promotions.
     */
    public function getActivePromotions(): Collection
    {
        return $this->promotionRepository->getActive();
    }

    /**
     * Get promotion by code.
     */
    public function getPromotionByCode(string $code): ?Promotion
    {
        return $this->promotionRepository->getByCode($code);
    }

    /**
     * Validate promotion code.
     * 
     * @throws BusinessException
     */
    public function validatePromotionCode(string $code, User $user, float $amount = 0): array
    {
        $promotion = $this->getPromotionByCode($code);
        
        if (!$promotion) {
            throw new BusinessException(
                __('promotions.not_found'),
                'Promotion Not Found',
                'PROMOTION_NOT_FOUND',
                404
            );
        }

        if (!$promotion->isValid()) {
            throw new BusinessException(
                __('promotions.expired'),
                'Promotion Expired',
                'PROMOTION_EXPIRED',
                422
            );
        }

        if (!$promotion->canBeUsedBy($user, $amount)) {
            throw new BusinessException(
                __('promotions.cannot_be_used'),
                'Promotion Cannot Be Used',
                'PROMOTION_CANNOT_BE_USED',
                422
            );
        }

        return [
            'valid' => true,
            'promotion' => $promotion,
            'discount_amount' => $promotion->calculateDiscount($amount)
        ];
    }

    /**
     * Apply promotion to booking.
     */
    public function applyPromotion(Promotion $promotion, int $bookingId, User $user, float $amount): float
    {
        $discountAmount = $promotion->calculateDiscount($amount);
        
        // Record promotion usage
        $this->promotionRepository->recordUsage($promotion, $user, $bookingId, $discountAmount);
        
        return $discountAmount;
    }

    /**
     * Get user's available promotions.
     */
    public function getUserPromotions(User $user): Collection
    {
        return $this->promotionRepository->getUserPromotions($user);
    }

    /**
     * Create a new promotion.
     */
    public function createPromotion(array $data): Promotion
    {
        return $this->promotionRepository->create($data);
    }

    /**
     * Update a promotion.
     */
    public function updatePromotion(int $id, array $data): ?Promotion
    {
        return $this->promotionRepository->update($id, $data);
    }

    /**
     * Delete a promotion.
     */
    public function deletePromotion(int $id): bool
    {
        return $this->promotionRepository->delete($id);
    }

    /**
     * Get promotion usage statistics.
     */
    public function getPromotionStats(Promotion $promotion): array
    {
        return $this->promotionRepository->getUsageStats($promotion);
    }
}
