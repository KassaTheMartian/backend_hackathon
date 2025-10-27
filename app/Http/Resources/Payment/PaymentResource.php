<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'payment_code' => $this->payment_code,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'transaction_id' => $this->transaction_id,
            'paid_at' => $this->paid_at?->toISOString(),
            'booking' => $this->whenLoaded('booking', function () {
                return [
                    'id' => $this->booking->id,
                    'booking_code' => $this->booking->booking_code,
                    'service_id' => $this->booking->service_id,
                ];
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

