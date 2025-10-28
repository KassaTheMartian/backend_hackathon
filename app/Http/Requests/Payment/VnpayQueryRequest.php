<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class VnpayQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_id' => ['required', 'string'],
            'guest_email' => ['nullable', 'email'],
            'guest_phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}


