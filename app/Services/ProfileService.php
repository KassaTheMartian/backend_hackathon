<?php

namespace App\Services;

use App\Models\User;
use App\Models\Promotion;
use Illuminate\Support\Facades\Hash;

class ProfileService
{
    /**
     * Get user profile with statistics.
     */
    public function getUserProfile(User $user): User
    {
        return $user->load(['bookings', 'reviews']);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Change user password.
     */
    public function changePassword(User $user, array $data): void
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new \Exception('Current password is incorrect');
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);
    }

    /**
     * Get user's available promotions.
     */
    public function getUserPromotions(User $user): array
    {
        return Promotion::active()
            ->valid()
            ->where(function ($query) use ($user) {
                $query->where('max_uses_per_user', '>', function ($subQuery) use ($user) {
                    $subQuery->selectRaw('COUNT(*)')
                        ->from('promotion_usages')
                        ->whereColumn('promotion_usages.promotion_id', 'promotions.id')
                        ->where('promotion_usages.user_id', $user->id);
                })
                ->orWhereNull('max_uses_per_user');
            })
            ->get()
            ->map(function ($promotion) use ($user) {
                $usedCount = $promotion->usages()
                    ->where('user_id', $user->id)
                    ->count();
                
                return [
                    'id' => $promotion->id,
                    'code' => $promotion->code,
                    'name' => $promotion->name['vi'] ?? $promotion->name['en'] ?? '',
                    'discount_type' => $promotion->discount_type,
                    'discount_value' => $promotion->discount_value,
                    'min_amount' => $promotion->min_amount,
                    'valid_from' => $promotion->valid_from->toISOString(),
                    'valid_to' => $promotion->valid_to->toISOString(),
                    'remaining_uses' => $promotion->max_uses_per_user ? 
                        max(0, $promotion->max_uses_per_user - $usedCount) : null,
                ];
            })
            ->toArray();
    }
}
