<?php

namespace App\Http\Resources\Promotion;

use App\Traits\HasLocalization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    use HasLocalization;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $locale = $this->getLocale($request);

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->getLocalizedValue($this->name, $locale),
            'description' => $this->getLocalizedValue($this->description, $locale),
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'min_amount' => $this->min_amount,
            'max_discount' => $this->max_discount,
            'max_uses' => $this->max_uses,
            'max_uses_per_user' => $this->max_uses_per_user,
            'used_count' => $this->used_count,
            'applicable_to' => $this->applicable_to,
            'applicable_services' => $this->applicable_services,
            'valid_from' => $this->valid_from?->toISOString(),
            'valid_to' => $this->valid_to?->toISOString(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
