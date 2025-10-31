<?php

namespace App\Services\Contracts;

use App\Data\Chat\ChatSessionData;
use App\Data\Chat\ChatMessageData;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Collection;

interface ChatRealTimeServiceInterface
{
    /** Create or get a guest chat session by session_key. */
    public function createGuestSession(ChatSessionData $data): ChatSession;

    /** Get guest chat history by session_key. */
    public function getGuestHistory(string $sessionKey): ?ChatSession;

    /** Persist a guest message using session_key in DTO. */
    public function guestSendMessage(ChatMessageData $data): ChatMessage;

    /** Get new messages by session_key for polling. */
    public function getNewMessages(string $sessionKey, int $lastMessageId = 0): Collection;

    /** Admin: list sessions with filters (mine|unassigned|all). */
    public function getAdminSessions(int $adminUserId, string $filter = 'unassigned'): Collection;

    /** Admin: assign a session to self by session_key. */
    public function assignSession(string $sessionKey, int $adminUserId): ChatSession;

    /** Admin: send message (will auto-assign if unassigned). */
    public function adminSendMessage(ChatMessageData $data, int $adminUserId): ChatMessage;

    /** Admin: get session messages by session_key. */
    public function getAdminSessionMessages(string $sessionKey, int $lastMessageId = 0): Collection;
}
