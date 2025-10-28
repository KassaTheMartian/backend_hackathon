<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class VnpayReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vnp_Amount' => ['required', 'string'],
            'vnp_OrderInfo' => ['required', 'string'],
            'vnp_ResponseCode' => ['required', 'string'],
            'vnp_TmnCode' => ['required', 'string'],
            'vnp_TxnRef' => ['required', 'string'],
            'vnp_SecureHash' => ['required', 'string'],
        ];
    }
}


