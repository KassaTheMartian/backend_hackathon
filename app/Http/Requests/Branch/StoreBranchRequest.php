<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|array',
            'name.vi' => 'required|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:branches,slug',
            'address' => 'required|array',
            'address.vi' => 'required|string|max:500',
            'address.en' => 'nullable|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'opening_hours' => 'nullable|array',
            'images' => 'nullable|array',
            'description' => 'nullable|array',
            'amenities' => 'nullable|array',
            'display_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên chi nhánh là bắt buộc',
            'name.vi.required' => 'Tên chi nhánh (tiếng Việt) là bắt buộc',
            'slug.required' => 'Slug là bắt buộc',
            'slug.unique' => 'Slug đã tồn tại',
            'address.required' => 'Địa chỉ là bắt buộc',
            'address.vi.required' => 'Địa chỉ (tiếng Việt) là bắt buộc',
            'phone.required' => 'Số điện thoại là bắt buộc',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
        ];
    }
}

