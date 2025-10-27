<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'min_amount',
        'max_discount',
        'max_uses',
        'max_uses_per_user',
        'used_count',
        'applicable_to',
        'applicable_services',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'discount_value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'max_uses' => 'integer',
        'max_uses_per_user' => 'integer',
        'used_count' => 'integer',
        'applicable_services' => 'array',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the usages of this promotion.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    /**
     * Scope a query to only include active promotions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('valid_from', '<=', now())
                    ->where('valid_to', '>=', now());
    }

    /**
     * Scope a query to only include valid promotions.
     */
    public function scopeValid($query)
    {
        return $query->where('valid_from', '<=', now())
                    ->where('valid_to', '>=', now());
    }

    /**
     * Check if the promotion is valid.
     */
    public function isValid(): bool
    {
        return $this->is_active &&
               $this->valid_from <= now() &&
               $this->valid_to >= now() &&
               (!$this->max_uses || $this->used_count < $this->max_uses);
    }

    /**
     * Check if the promotion can be used by a user.
     */
    public function canBeUsedBy(User $user, float $amount = 0): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($amount > 0 && $amount < $this->min_amount) {
            return false;
        }

        if ($this->max_uses_per_user) {
            $userUsageCount = $this->usages()
                ->where('user_id', $user->id)
                ->count();

            if ($userUsageCount >= $this->max_uses_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate discount amount for given price.
     */
    public function calculateDiscount(float $price): float
    {
        if ($price < $this->min_amount) {
            return 0;
        }

        $discount = 0;
        if ($this->discount_type === 'percentage') {
            $discount = ($price * $this->discount_value) / 100;
        } else {
            $discount = $this->discount_value;
        }

        // Apply max discount limit if set
        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        return min($discount, $price);
    }

    /**
     * Check if promotion applies to a specific service.
     */
    public function appliesToService(int $serviceId): bool
    {
        if ($this->applicable_to === 'all') {
            return true;
        }

        if ($this->applicable_to === 'services' && $this->applicable_services) {
            return in_array($serviceId, $this->applicable_services);
        }

        return false;
    }
}
