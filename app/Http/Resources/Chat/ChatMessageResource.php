<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chat_session_id' => $this->chat_session_id,
            'user_id' => $this->user_id,
            'role' => $this->role, // 'user' | 'assistant' | 'staff'
            'message' => $this->message,
            'meta' => $this->meta,
            'is_bot' => $this->role === 'assistant',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
