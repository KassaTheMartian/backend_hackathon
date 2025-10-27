<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:service_categories,id',
            'name' => 'required|array',
            'name.vi' => 'required|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'name.ja' => 'nullable|string|max:255',
            'name.zh' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:services,slug',
            'description' => 'nullable|array',
            'description.vi' => 'nullable|string',
            'description.en' => 'nullable|string',
            'description.ja' => 'nullable|string',
            'description.zh' => 'nullable|string',
            'short_description' => 'nullable|array',
            'short_description.vi' => 'nullable|string|max:500',
            'short_description.en' => 'nullable|string|max:500',
            'short_description.ja' => 'nullable|string|max:500',
            'short_description.zh' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0|lt:price',
            'duration' => 'required|integer|min:1',
            'image' => 'nullable|string|max:500',
            'gallery' => 'nullable|array',
            'gallery.*' => 'string|max:500',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'display_order' => 'integer|min:0',
            'meta_title' => 'nullable|array',
            'meta_description' => 'nullable|array',
            'meta_keywords' => 'nullable|array',
        ];
    }
}