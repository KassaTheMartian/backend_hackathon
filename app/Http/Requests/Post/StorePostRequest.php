<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:post_categories,id',
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.vi' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug',
            'content' => 'required|array',
            'content.en' => 'required|string',
            'content.vi' => 'required|string',
            'excerpt' => 'nullable|array',
            'excerpt.en' => 'nullable|string|max:500',
            'excerpt.vi' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:255',
            'meta_title' => 'nullable|array',
            'meta_title.en' => 'nullable|string|max:255',
            'meta_title.vi' => 'nullable|string|max:255',
            'meta_description' => 'nullable|array',
            'meta_description.en' => 'nullable|string|max:500',
            'meta_description.vi' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'meta_keywords.en' => 'nullable|string',
            'meta_keywords.vi' => 'nullable|string',
            'is_featured' => 'boolean',
            'status' => 'required|in:draft,published',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50'
        ];
    }
}

