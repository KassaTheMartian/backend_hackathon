<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class VnpayRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_id' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email'],
            'guest_phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}


