<?php

namespace App\Http\Requests\Chatbot;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow both authenticated and guest users
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
            'message' => 'required|string|min:1|max:1000',
            // session_key: optional string provided by guest clients (stored in localStorage/cookie)
            'session_key' => 'nullable|string|max:191',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'message.required' => __('chatbot.message_required'),
            'message.string' => __('chatbot.message_string'),
            'message.min' => __('chatbot.message_min'),
            'message.max' => __('chatbot.message_max'),
        ];
    }
}
