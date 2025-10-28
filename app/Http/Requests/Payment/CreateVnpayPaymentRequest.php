<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CreateVnpayPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'bank_code' => ['nullable', 'string', 'in:,VNPAYQR,VNBANK,INTCARD'],
            'language' => ['nullable', 'string', 'in:vi,en'],
            'guest_email' => ['nullable', 'email'],
            'guest_phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}


