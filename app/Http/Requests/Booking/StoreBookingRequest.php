<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

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
        $isGuest = !auth()->check();
        
        return [
            // Guest information (required if user not authenticated)
            'guest_name' => $isGuest ? 'required|string|max:255' : 'nullable|string|max:255',
            'guest_email' => $isGuest ? 'required|email|max:255' : 'nullable|email|max:255',
            'guest_phone' => $isGuest ? 'required|string|max:20' : 'nullable|string|max:20',
            
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
        return [
            'guest_name.required' => 'Vui lòng nhập họ tên của bạn.',
            'guest_email.required' => 'Vui lòng nhập email của bạn.',
            'guest_email.email' => 'Email không hợp lệ.',
            'guest_phone.required' => 'Vui lòng nhập số điện thoại của bạn.',
            'branch_id.required' => 'Vui lòng chọn chi nhánh.',
            'branch_id.exists' => 'Chi nhánh không tồn tại.',
            'service_id.required' => 'Vui lòng chọn dịch vụ.',
            'service_id.exists' => 'Dịch vụ không tồn tại.',
            'staff_id.required' => 'Vui lòng chọn nhân viên.',
            'staff_id.exists' => 'Nhân viên không tồn tại.',
            'booking_date.required' => 'Vui lòng chọn ngày đặt lịch.',
            'booking_date.after_or_equal' => 'Ngày đặt lịch phải là hôm nay hoặc trong tương lai.',
            'booking_time.required' => 'Vui lòng chọn giờ đặt lịch.',
            'booking_time.date_format' => 'Giờ đặt lịch phải có định dạng HH:MM.',
        ];
    }
}