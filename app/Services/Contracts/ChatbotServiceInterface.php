<?php

namespace App\Services\Contracts;

interface ChatbotServiceInterface
{
    /**
     * Process chat message and generate response.
     *
     * @param string $message User's message
     * @param string $locale Language (vi or en)
     * @param int|null $userId User ID if authenticated
     * @return array Response data
     */
    public function chat(string $message, string $locale = 'vi', ?int $userId = null, ?string $sessionKey = null): array;

    /**
     * Get conversation context for the chatbot.
     *
     * @param string $locale Language (vi or en)
     * @return string Context string
     */
    public function getContext(string $locale = 'vi'): string;
}
