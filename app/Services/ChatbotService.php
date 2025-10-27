<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Services\Contracts\ChatbotServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ChatbotService implements ChatbotServiceInterface
{
    public function __construct(
        private ChatRepositoryInterface $chatRepository
    ) {}

    /**
     * Get chat sessions for user.
     */
    public function getUserSessions(int $userId): Collection
    {
        return $this->chatRepository->getUserSessions($userId);
    }

    /**
     * Get session by ID.
     */
    public function getSessionById(int $id): ?ChatSession
    {
        return $this->chatRepository->getSessionById($id);
    }

    /**
     * Create a new chat session.
     */
    public function createSession(int $userId, string $title = null): ChatSession
    {
        return $this->chatRepository->createSession($userId, $title);
    }

    /**
     * Get messages for session.
     */
    public function getSessionMessages(int $sessionId): Collection
    {
        return $this->chatRepository->getSessionMessages($sessionId);
    }

    /**
     * Send a message.
     */
    public function sendMessage(int $sessionId, string $content, string $type = 'user'): ChatMessage
    {
        return $this->chatRepository->sendMessage($sessionId, $content, $type);
    }

    /**
     * Process bot response.
     */
    public function processBotResponse(int $sessionId, string $userMessage): ChatMessage
    {
        // Simple bot logic - in real implementation, this would integrate with AI service
        $botResponse = $this->generateBotResponse($userMessage);
        
        return $this->chatRepository->sendMessage($sessionId, $botResponse, 'bot');
    }

    /**
     * Delete a session.
     */
    public function deleteSession(int $id): bool
    {
        $session = $this->chatRepository->getSessionById($id);
        if (!$session) {
            return false;
        }
        return $this->chatRepository->deleteSession($session);
    }

    /**
     * Clear session messages.
     */
    public function clearSessionMessages(int $id): bool
    {
        $session = $this->chatRepository->getSessionById($id);
        if (!$session) {
            return false;
        }
        return $this->chatRepository->clearSessionMessages($session);
    }

    /**
     * Generate simple bot response.
     */
    private function generateBotResponse(string $userMessage): string
    {
        $message = strtolower($userMessage);
        
        if (str_contains($message, 'hello') || str_contains($message, 'hi')) {
            return 'Hello! How can I help you today?';
        }
        
        if (str_contains($message, 'booking') || str_contains($message, 'appointment')) {
            return 'I can help you with booking information. What service are you interested in?';
        }
        
        if (str_contains($message, 'price') || str_contains($message, 'cost')) {
            return 'You can find our service prices on our website. Would you like me to help you with a specific service?';
        }
        
        if (str_contains($message, 'location') || str_contains($message, 'address')) {
            return 'We have multiple branches. You can find our locations on our website. Which area are you looking for?';
        }
        
        return 'Thank you for your message. Our team will get back to you soon. Is there anything else I can help you with?';
    }
}