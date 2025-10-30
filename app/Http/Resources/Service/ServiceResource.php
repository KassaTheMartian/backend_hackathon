<?php

namespace App\Http\Resources\Service;

use App\Traits\HasLocalization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    use HasLocalization;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $this->getLocale($request);
        
        return [
            'id' => $this->id,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->getLocalizedValue($this->category->name, $locale),
                'slug' => $this->category->slug,
            ],
            'name' => $this->getLocalizedValue($this->name, $locale),
            'slug' => $this->slug,
            'description' => $this->getLocalizedValue($this->description, $locale),
            'short_description' => $this->getLocalizedValue($this->short_description, $locale),
            'price' => $this->price,
            'discounted_price' => $this->discounted_price,
            'final_price' => $this->final_price,
            'discount_percentage' => $this->discount_percentage,
            'duration' => $this->duration,
            'image' => $this->image ? (filter_var($this->image, FILTER_VALIDATE_URL) ? $this->image : url($this->image)) : null,
            'gallery' => $this->gallery ?? [],
            'is_featured' => $this->is_featured,
            'rating' => $this->whenLoaded('reviews', function () {
                return $this->reviews->avg('rating') ?? 0;
            }),
            'total_reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->count();
            }),
            'available_branches' => $this->whenLoaded('branches', function () use ($locale) {
                return $this->branches->map(function ($branch) use ($locale) {
                    return [
                        'id' => $branch->id,
                        'name' => $this->getLocalizedValue($branch->name, $locale),
                        'address' => $this->getLocalizedValue($branch->address, $locale),
                    ];
                });
            }),
            'related_services' => $this->when($this->relationLoaded('related_services'), function () use ($locale) {
                return $this->related_services->map(function ($service) use ($locale) {
                    return [
                        'id' => $service->id,
                        'name' => $this->getLocalizedValue($service->name, $locale),
                        'slug' => $service->slug,
                        'price' => $service->price,
                        'image' => $service->image,
                    ];
                });
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}