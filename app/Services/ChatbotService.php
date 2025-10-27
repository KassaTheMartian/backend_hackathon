<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Str;

class ChatbotService
{
    /**
     * Process a message from the user.
     */
    public function processMessage(array $data): array
    {
        $sessionId = $data['session_id'] ?? $this->createSession();
        $message = $data['message'];
        $context = $data['context'] ?? [];

        // Get or create session
        $session = ChatSession::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => auth()->id(),
                'guest_name' => $data['guest_name'] ?? null,
                'guest_email' => $data['guest_email'] ?? null,
            ]
        );

        // Save user message
        $userMessage = $session->messages()->create([
            'sender_id' => auth()->id(),
            'sender_type' => 'user',
            'message' => $message,
            'message_type' => 'text',
            'is_bot' => false,
        ]);

        // Process with AI (mock response for now)
        $botResponse = $this->generateBotResponse($message, $context, $session);

        // Save bot response
        $botMessage = $session->messages()->create([
            'sender_type' => 'bot',
            'message' => $botResponse['message'],
            'message_type' => 'text',
            'is_bot' => true,
            'bot_confidence' => $botResponse['confidence'],
        ]);

        return [
            'session_id' => $sessionId,
            'bot_response' => $botResponse['message'],
            'suggestions' => $botResponse['suggestions'] ?? [],
            'intent' => $botResponse['intent'] ?? 'general',
            'confidence' => $botResponse['confidence'],
        ];
    }

    /**
     * Get chat history for a session.
     */
    public function getChatHistory(string $sessionId): array
    {
        $session = ChatSession::where('session_id', $sessionId)->first();
        
        if (!$session) {
            return [
                'session_id' => $sessionId,
                'messages' => [],
            ];
        }

        $messages = $session->messages()
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_type' => $message->sender_type,
                    'message' => $message->message,
                    'created_at' => $message->created_at->toISOString(),
                ];
            });

        return [
            'session_id' => $sessionId,
            'messages' => $messages,
        ];
    }

    /**
     * Generate bot response (mock implementation).
     */
    private function generateBotResponse(string $message, array $context, ChatSession $session): array
    {
        $message = strtolower($message);
        
        // Simple keyword matching for demo
        if (str_contains($message, 'đặt lịch') || str_contains($message, 'booking')) {
            return [
                'message' => 'Chào bạn! Tôi có thể giúp bạn đặt lịch hẹn. Bạn muốn đặt lịch cho dịch vụ nào?',
                'suggestions' => ['Điều trị mụn', 'Chăm sóc da', 'Làm trắng da'],
                'intent' => 'booking_inquiry',
                'confidence' => 0.9,
            ];
        }
        
        if (str_contains($message, 'giá') || str_contains($message, 'price')) {
            return [
                'message' => 'Bạn có thể xem giá các dịch vụ tại trang dịch vụ của chúng tôi. Bạn quan tâm đến dịch vụ nào?',
                'suggestions' => ['Xem dịch vụ', 'Liên hệ tư vấn'],
                'intent' => 'price_inquiry',
                'confidence' => 0.8,
            ];
        }
        
        if (str_contains($message, 'địa chỉ') || str_contains($message, 'address')) {
            return [
                'message' => 'Chúng tôi có nhiều chi nhánh tại TP.HCM. Bạn muốn tìm chi nhánh gần nhất không?',
                'suggestions' => ['Tìm chi nhánh', 'Xem bản đồ'],
                'intent' => 'location_inquiry',
                'confidence' => 0.8,
            ];
        }
        
        return [
            'message' => 'Xin chào! Tôi có thể giúp bạn đặt lịch hẹn, tư vấn dịch vụ hoặc trả lời các câu hỏi khác. Bạn cần hỗ trợ gì?',
            'suggestions' => ['Đặt lịch hẹn', 'Xem dịch vụ', 'Liên hệ tư vấn'],
            'intent' => 'general',
            'confidence' => 0.7,
        ];
    }

    /**
     * Create a new chat session.
     */
    private function createSession(): string
    {
        return ChatSession::generateSessionId();
    }
}
