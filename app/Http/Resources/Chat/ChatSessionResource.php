<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatSessionResource extends JsonResource
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
            'user_id' => $this->user_id,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'metadata' => $this->metadata,
            'user' => $this->whenLoaded('user'),
            'assigned_staff' => $this->whenLoaded('assignedTo'),
            'messages_count' => $this->when(isset($this->messages_count), $this->messages_count),
            'last_message' => $this->whenLoaded('messages', function () {
                return $this->messages->first();
            }),
        ];
    }
}
