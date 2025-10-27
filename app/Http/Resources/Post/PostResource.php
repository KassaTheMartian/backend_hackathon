<?php

namespace App\Http\Resources\Post;

use App\Traits\HasLocalization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    use HasLocalization;

    public function toArray(Request $request): array
    {
        $locale = $this->getLocale($request);
        
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', function () use ($locale) {
                return [
                    'id' => $this->category->id,
                    'name' => $this->getLocalizedValue($this->category->name, $locale),
                    'slug' => $this->category->slug,
                ];
            }),
            'title' => $this->getLocalizedValue($this->title, $locale),
            'slug' => $this->getLocalizedValue($this->slug, $locale),
            'content' => $this->getLocalizedValue($this->content, $locale),
            'excerpt' => $this->getLocalizedValue($this->excerpt, $locale),
            'featured_image' => $this->featured_image,
            'meta_title' => $this->getLocalizedValue($this->meta_title, $locale),
            'meta_description' => $this->getLocalizedValue($this->meta_description, $locale),
            'meta_keywords' => $this->getLocalizedValue($this->meta_keywords, $locale),
            'is_featured' => $this->is_featured,
            'status' => $this->status,
            'views_count' => $this->views_count,
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->pluck('name');
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}


