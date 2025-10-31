<?php

namespace App\Data\Chat;

use Spatie\LaravelData\Data;

class ChatMessageData extends Data
{
    public function __construct(
        public string $session_key,
        public ?int $user_id,
        public string $role,
        public string $message,
        public ?array $meta = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            session_key: $data['session_key'],
            user_id: $data['user_id'] ?? null,
            role: $data['role'],
            message: $data['message'],
            meta: $data['meta'] ?? null,
        );
    }
}
