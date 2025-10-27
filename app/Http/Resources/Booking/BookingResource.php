<?php

namespace App\Http\Resources\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'user_id' => $this->user_id,
            'service_id' => $this->service_id,
            'staff_id' => $this->staff_id,
            'branch_id' => $this->branch_id,
            'booking_date' => $this->booking_date,
            'booking_time' => $this->booking_time,
            'duration' => $this->duration,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'guest_name' => $this->guest_name,
            'guest_phone' => $this->guest_phone,
            'guest_email' => $this->guest_email,
            'notes' => $this->notes,
            'service' => $this->whenLoaded('service', function () {
                return [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                    'price' => $this->service->price,
                ];
            }),
            'branch' => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                    'address' => $this->branch->address,
                ];
            }),
            'staff' => $this->whenLoaded('staff', function () {
                return [
                    'id' => $this->staff->id,
                    'name' => $this->staff->name,
                ];
            }),
            'payment' => $this->whenLoaded('payment', function () {
                return [
                    'id' => $this->payment->id,
                    'status' => $this->payment->status,
                    'amount' => $this->payment->amount,
                ];
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

