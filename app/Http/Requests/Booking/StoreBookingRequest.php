<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            // Guest information (required if user not authenticated)
            'guest_name' => 'required_if:user_id,null|string|max:255',
            'guest_email' => 'required_if:user_id,null|email|max:255',
            'guest_phone' => 'required_if:user_id,null|string|max:20',
            
            // Booking details
            'branch_id' => 'required|exists:branches,id',
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|exists:staff,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
            
            // Promotion
            'promotion_code' => 'nullable|string|exists:promotions,code',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'guest_name.required_if' => 'Guest name is required for non-authenticated users.',
            'guest_email.required_if' => 'Guest email is required for non-authenticated users.',
            'guest_phone.required_if' => 'Guest phone is required for non-authenticated users.',
            'booking_date.after_or_equal' => 'Booking date must be today or in the future.',
            'booking_time.date_format' => 'Booking time must be in HH:MM format.',
        ];
    }
}