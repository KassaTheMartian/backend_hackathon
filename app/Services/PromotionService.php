<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\User;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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
     */
    public function validatePromotionCode(string $code, User $user, float $amount = 0): array
    {
        $promotion = $this->getPromotionByCode($code);
        
        if (!$promotion) {
            return [
                'valid' => false,
                'message' => 'Mã khuyến mãi không tồn tại'
            ];
        }

        if (!$promotion->isValid()) {
            return [
                'valid' => false,
                'message' => 'Mã khuyến mãi đã hết hạn hoặc không còn hiệu lực'
            ];
        }

        if (!$promotion->canBeUsedBy($user, $amount)) {
            return [
                'valid' => false,
                'message' => 'Mã khuyến mãi không thể sử dụng cho đơn hàng này'
            ];
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
