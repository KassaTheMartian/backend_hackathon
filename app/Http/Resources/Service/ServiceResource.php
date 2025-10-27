<?php

namespace App\Http\Resources\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = $request->get('locale', 'vi');
        
        return [
            'id' => $this->id,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name[$locale] ?? $this->category->name['vi'] ?? '',
                'slug' => $this->category->slug,
            ],
            'name' => $this->name[$locale] ?? $this->name['vi'] ?? '',
            'slug' => $this->slug,
            'description' => $this->description[$locale] ?? $this->description['vi'] ?? '',
            'short_description' => $this->short_description[$locale] ?? $this->short_description['vi'] ?? '',
            'price' => $this->price,
            'discounted_price' => $this->discounted_price,
            'final_price' => $this->final_price,
            'discount_percentage' => $this->discount_percentage,
            'duration' => $this->duration,
            'image' => $this->image,
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
                        'name' => $branch->name[$locale] ?? $branch->name['vi'] ?? '',
                        'address' => $branch->address[$locale] ?? $branch->address['vi'] ?? '',
                    ];
                });
            }),
            'related_services' => $this->when($this->relationLoaded('related_services'), function () use ($locale) {
                return $this->related_services->map(function ($service) use ($locale) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name[$locale] ?? $service->name['vi'] ?? '',
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