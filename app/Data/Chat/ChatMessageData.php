<?php

namespace App\Data\Chat;

use Spatie\LaravelData\Data;

class ChatMessageData extends Data
{
    public function __construct(
        public int $session_id,
        public ?int $sender_id,
        public string $sender_type,
        public string $message,
        public ?string $message_type = null,
        public bool $is_bot = false,
        public ?float $bot_confidence = null,
        public ?array $metadata = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            session_id: $data['session_id'],
            sender_id: $data['sender_id'] ?? null,
            sender_type: $data['sender_type'],
            message: $data['message'],
            message_type: $data['message_type'] ?? null,
            is_bot: $data['is_bot'] ?? false,
            bot_confidence: $data['bot_confidence'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
