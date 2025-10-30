<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChatSessionRequest extends FormRequest
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
            'status' => 'sometimes|string|in:active,closed,transferred',
            'assigned_to' => 'sometimes|integer|exists:staff,id',
            'metadata' => 'sometimes|array',
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
            'status.string' => __('chat_realtime.status_string'),
            'status.in' => __('chat_realtime.status_in'),
            'assigned_to.integer' => __('chat_realtime.assigned_to_integer'),
            'assigned_to.exists' => __('chat_realtime.assigned_to_exists'),
            'metadata.array' => __('chat_realtime.metadata_array'),
        ];
    }
}
