<?php

namespace App\Http\Requests\Chatbot;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:2000',
            'session_id' => 'required|exists:chat_sessions,id'
        ];
    }
}

