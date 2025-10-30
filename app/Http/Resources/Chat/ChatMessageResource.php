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
            'session_id' => $this->session_id,
            'sender_id' => $this->sender_id,
            'sender_type' => $this->sender_type,
            'message' => $this->message,
            'message_type' => $this->message_type,
            'is_bot' => $this->is_bot,
            'bot_confidence' => $this->bot_confidence,
            'metadata' => $this->metadata,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'sender' => $this->whenLoaded('sender'),
        ];
    }
}
