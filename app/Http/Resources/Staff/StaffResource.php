<?php

namespace App\Http\Resources\Staff;

use App\Traits\HasLocalization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    use HasLocalization;

    public function toArray(Request $request): array
    {
        $locale = $this->getLocale($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'position' => $this->position,
            'years_of_experience' => $this->years_of_experience,
            'rating' => $this->rating,
            'total_reviews' => $this->total_reviews,
            'is_active' => $this->is_active,
            'branch' => $this->whenLoaded('branch', function () {
                $locale = $this->getLocale(request());
                return [
                    'id' => $this->branch->id,
                    'name' => $this->getLocalizedValue($this->branch->name, $locale),
                ];
            }),
            'services' => $this->whenLoaded('services', function () {
                $locale = $this->getLocale(request());
                return $this->services->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $this->getLocalizedValue($s->name, $locale),
                ])->values();
            }),
        ];
    }
}


