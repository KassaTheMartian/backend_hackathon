<?php

namespace App\Repositories\Contracts;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Collection;

interface ChatRepositoryInterface
{
    public function getUserSessions(int $userId): Collection;
    public function getSessionById(int $id): ?ChatSession;
    public function createSession(int $userId, string $title = null): ChatSession;
    public function getSessionMessages(int $sessionId): Collection;
    public function sendMessage(int $sessionId, string $content, string $type = 'user'): ChatMessage;
    public function deleteSession(ChatSession $session): bool;
    public function clearSessionMessages(ChatSession $session): bool;
}

