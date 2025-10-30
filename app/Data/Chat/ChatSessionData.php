<?php

namespace App\Data\Chat;

use Spatie\LaravelData\Data;

class ChatSessionData extends Data
{
    public function __construct(
        public string $session_id,
        public ?int $user_id,
        public ?string $guest_name,
        public ?string $guest_email,
        public ?string $guest_phone,
        public string $status = 'active',
        public ?int $assigned_to = null,
        public ?array $metadata = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            session_id: $data['session_id'],
            user_id: $data['user_id'] ?? null,
            guest_name: $data['guest_name'] ?? null,
            guest_email: $data['guest_email'] ?? null,
            guest_phone: $data['guest_phone'] ?? null,
            status: $data['status'] ?? 'active',
            assigned_to: $data['assigned_to'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
