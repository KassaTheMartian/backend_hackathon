<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class CreateGuestSessionRequest extends FormRequest
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
        return [
            'session_id' => 'required|string|max:100',
            'guest_name' => 'nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'session_id.required' => __('chat_realtime.session_id_required'),
            'session_id.string' => __('chat_realtime.session_id_string'),
            'session_id.max' => __('chat_realtime.session_id_max'),
            'guest_name.string' => __('chat_realtime.guest_name_string'),
            'guest_name.max' => __('chat_realtime.guest_name_max'),
            'guest_email.email' => __('chat_realtime.guest_email_email'),
            'guest_email.max' => __('chat_realtime.guest_email_max'),
            'guest_phone.string' => __('chat_realtime.guest_phone_string'),
            'guest_phone.max' => __('chat_realtime.guest_phone_max'),
        ];
    }
}
