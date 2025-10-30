<?php

namespace App\Http\Resources\Booking;

use App\Traits\HasLocalization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    use HasLocalization;

    public function toArray(Request $request): array
    {
        $locale = $this->getLocale($request);
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
            'payment_status' => $this->payment_status,
            'service' => $this->whenLoaded('service', function () use ($locale) {
                return [
                    'id' => $this->service->id,
                    'name' => is_array($this->service->name)
                        ? $this->getLocalizedValue($this->service->name, $locale)
                        : $this->service->name,
                    'price' => $this->service->price,
                ];
            }),
            'branch' => $this->whenLoaded('branch', function () use ($locale) {
                return [
                    'id' => $this->branch->id,
                    'name' => is_array($this->branch->name)
                        ? $this->getLocalizedValue($this->branch->name, $locale)
                        : $this->branch->name,
                    'address' => is_array($this->branch->address)
                        ? $this->getLocalizedValue($this->branch->address, $locale)
                        : $this->branch->address,
                ];
            }),
            'staff' => $this->whenLoaded('staff.user', function () use ($locale) {
                return [
                    'id' => $this->staff->id,
                    'name' => is_array($this->staff->name)
                        ? $this->getLocalizedValue($this->staff->name, $locale)
                        : $this->staff->name,
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

