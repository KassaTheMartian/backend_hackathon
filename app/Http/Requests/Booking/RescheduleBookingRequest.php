<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'booking_date' => ['required', 'date', 'after:now'],
            'booking_time' => ['required', 'date_format:H:i'],
            'staff_id' => ['nullable', 'integer', 'exists:staff,id'],
        ];
    }
}


