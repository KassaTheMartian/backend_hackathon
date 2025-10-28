<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
        $isGuest = !Auth::check();
        
        return [
            // Guest information (required if user not authenticated)
            'guest_name' => $isGuest ? 'required|string|max:255' : 'nullable|string|max:255',
            'guest_email' => $isGuest ? 'required|email|max:255' : 'nullable|email|max:255',
            'guest_phone' => $isGuest ? 'required|string|max:20' : 'nullable|string|max:20',
            'guest_email_otp' => $isGuest ? 'required|string|size:6' : 'nullable|string|size:6',
            
            // Booking details
            'branch_id' => 'required|exists:branches,id',
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'required|exists:staff,id',
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
        return [];
    }
}