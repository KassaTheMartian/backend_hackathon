<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
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
        $branchId = $this->route('branch');
        
        return [
            'name' => 'sometimes|array',
            'name.vi' => 'required_with:name|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('branches', 'slug')->ignore($branchId)
            ],
            'address' => 'sometimes|array',
            'address.vi' => 'required_with:address|string|max:500',
            'address.en' => 'nullable|string|max:500',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255',
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
            'name.vi.required_with' => 'Tên chi nhánh (tiếng Việt) là bắt buộc khi cập nhật tên',
            'slug.unique' => 'Slug đã tồn tại',
            'address.vi.required_with' => 'Địa chỉ (tiếng Việt) là bắt buộc khi cập nhật địa chỉ',
            'email.email' => 'Email không hợp lệ',
        ];
    }
}

