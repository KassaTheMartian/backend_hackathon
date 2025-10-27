<?php

namespace App\Http\Resources\Chatbot;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'status' => $this->status,
            'messages' => $this->whenLoaded('messages', function () {
                return $this->messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'type' => $message->type,
                        'created_at' => $message->created_at->toISOString(),
                    ];
                });
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

