<?php

namespace App\Http\Resources\Review;

use App\Traits\HasLocalization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    use HasLocalization;

    public function toArray(Request $request): array
    {
        $locale = $this->getLocale($request);
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'booking_id' => $this->booking_id,
            'service_id' => $this->service_id,
            'branch_id' => $this->branch_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_published' => $this->is_published,
            'user' => $this->whenLoaded('user', function () use ($locale) {
                return [
                    'id' => $this->user->id,
                    'name' => is_array($this->user->name)
                        ? $this->getLocalizedValue($this->user->name, $locale)
                        : $this->user->name,
                ];
            }),
            'service' => $this->whenLoaded('service', function () use ($locale) {
                return [
                    'id' => $this->service->id,
                    'name' => is_array($this->service->name)
                        ? $this->getLocalizedValue($this->service->name, $locale)
                        : $this->service->name,
                ];
            }),
            'branch' => $this->whenLoaded('branch', function () use ($locale) {
                return [
                    'id' => $this->branch->id,
                    'name' => is_array($this->branch->name)
                        ? $this->getLocalizedValue($this->branch->name, $locale)
                        : $this->branch->name,
                ];
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

