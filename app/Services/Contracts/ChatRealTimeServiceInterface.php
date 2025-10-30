<?php

namespace App\Services\Contracts;

use App\Data\Chat\ChatSessionData;
use App\Data\Chat\ChatMessageData;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface ChatRealTimeServiceInterface
{
    /**
     * Create a guest chat session.
     */
    public function createGuestSession(ChatSessionData $data): ChatSession;

    /**
     * Get guest chat history.
     */
    public function getGuestHistory(string $sessionId): ?ChatSession;

    /**
     * Send message as guest.
     */
    public function guestSendMessage(ChatMessageData $data): ChatMessage;

    /**
     * Transfer chat to human staff.
     */
    public function transferToHuman(int $sessionId): array;

    /**
     * Send message as staff.
     */
    public function staffSendMessage(ChatMessageData $data, int $staffUserId): ChatMessage;

    /**
     * Get new messages for polling.
     */
    public function getNewMessages(int $sessionId, int $lastMessageId = 0): Collection;

    /**
     * Get sessions assigned to staff.
     */
    public function getStaffSessions(int $staffUserId): Collection;
}
