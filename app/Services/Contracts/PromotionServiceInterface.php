<?php

namespace App\Services\Contracts;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface PromotionServiceInterface
{
    public function getActivePromotions(): Collection;
    public function getPromotionByCode(string $code): ?Promotion;
    public function validatePromotionCode(string $code, User $user, float $amount = 0): array;
    public function applyPromotion(Promotion $promotion, int $bookingId, User $user, float $amount): float;
    public function getUserPromotions(User $user): Collection;
    public function createPromotion(array $data): Promotion;
    public function updatePromotion(int $id, array $data): ?Promotion;
    public function deletePromotion(int $id): bool;
    public function getPromotionStats(Promotion $promotion): array;
}
