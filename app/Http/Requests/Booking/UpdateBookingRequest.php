<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'sometimes|exists:services,id',
            'staff_id' => 'nullable|exists:staff,id',
            'branch_id' => 'sometimes|exists:branches,id',
            'booking_date' => 'sometimes|date|after:now',
            'booking_time' => 'sometimes|date_format:H:i',
            'guest_name' => 'sometimes|string|max:255',
            'guest_phone' => 'sometimes|string|max:20',
            'guest_email' => 'sometimes|email|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
        ];
    }
}

