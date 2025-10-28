<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class AvailableSlotsRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date|after_or_equal:today',
            'service_id' => 'required|integer|exists:services,id',
            'staff_id' => 'sometimes|nullable|integer|exists:staff,id',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Date is required',
            'date.after_or_equal' => 'Date must be today or a future date',
            'service_id.required' => 'Service is required',
            'service_id.exists' => 'Selected service does not exist',
            'staff_id.exists' => 'Selected staff does not exist',
        ];
    }
}
