<?php

namespace App\Http\Resources\Branch;

use App\Traits\HasLocalization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    use HasLocalization;

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $locale = $this->getLocale($request);
        
        return [
            'id' => $this->id,
            'name' => $this->getLocalizedValue($this->name, $locale),
            'slug' => $this->slug,
            'address' => $this->getLocalizedValue($this->address, $locale),
            'phone' => $this->phone,
            'email' => $this->email,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'opening_hours' => $this->opening_hours,
            'images' => $this->images,
            'description' => $this->getLocalizedValue($this->description, $locale),
            'amenities' => $this->amenities,
            'is_active' => $this->is_active,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}


