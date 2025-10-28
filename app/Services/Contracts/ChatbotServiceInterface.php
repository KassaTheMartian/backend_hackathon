<?php

namespace App\Services\Contracts;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Collection;

interface ChatbotServiceInterface
{
    /**
     * Get chat sessions for user.
     */
    public function getUserSessions(int $userId): Collection;

    /**
     * Get session by ID.
     */
    public function getSessionById(int $id): ?ChatSession;

    /**
     * Create a new chat session.
     */
    public function createSession(int $userId, string $title = null): ChatSession;

    /**
     * Get messages for session.
     */
    public function getSessionMessages(int $sessionId): Collection;

    /**
     * Send a message.
     */
    public function sendMessage(int $sessionId, string $content, string $type = 'user'): ChatMessage;

    /**
     * Process bot response with optional mode:
     * - booking: suggest services for booking
     * - faq: answer booking-related FAQs
     * - human: escalate to staff
     */
    public function processBotResponse(int $sessionId, string $userMessage, ?string $mode = null): ChatMessage;

    /**
     * Delete a session.
     */
    public function deleteSession(int $id): bool;

    /**
     * Clear session messages.
     */
    public function clearSessionMessages(int $id): bool;
}
