<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Service as ServiceModel;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Services\Contracts\ChatbotServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

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
    public function processBotResponse(int $sessionId, string $userMessage, ?string $mode = null): ChatMessage
    {
        // Mode routing: booking | faq | human | default
        $mode = $mode ?: $this->inferMode($userMessage);

        if ($mode === 'human') {
            $response = __('chatbot.switched_to_human');
            return $this->chatRepository->sendMessage($sessionId, $response, 'bot');
        }

        if ($mode === 'booking') {
            $response = $this->generateBookingSuggestions($userMessage);
            return $this->chatRepository->sendMessage($sessionId, $response, 'bot');
        }

        if ($mode === 'faq') {
            $response = $this->generateFaqAnswer($userMessage);
            return $this->chatRepository->sendMessage($sessionId, $response, 'bot');
        }

        $response = $this->generateBotResponse($userMessage);
        return $this->chatRepository->sendMessage($sessionId, $response, 'bot');
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
            return __('chatbot.greet');
        }
        
        if (str_contains($message, 'booking') || str_contains($message, 'appointment')) {
            return __('chatbot.intent_booking');
        }
        
        if (str_contains($message, 'price') || str_contains($message, 'cost')) {
            return __('chatbot.intent_price');
        }
        
        if (str_contains($message, 'location') || str_contains($message, 'address')) {
            return __('chatbot.intent_location');
        }
        
        return __('chatbot.generic_reply');
    }

    private function inferMode(string $message): string
    {
        $m = strtolower($message);
        if (str_contains($m, 'nhân viên') || str_contains($m, 'human') || str_contains($m, 'agent')) {
            return 'human';
        }
        if (str_contains($m, 'đặt lịch') || str_contains($m, 'booking') || str_contains($m, 'dịch vụ')) {
            return 'booking';
        }
        if (str_contains($m, 'cách đặt') || str_contains($m, 'hướng dẫn') || str_contains($m, 'faq')) {
            return 'faq';
        }
        return 'default';
    }

    private function generateBookingSuggestions(string $userMessage): string
    {
        // Try Gemini first if configured, otherwise fallback to local search
        $services = $this->suggestServicesWithGemini($userMessage);
        if (!$services) {
            $services = $this->suggestServicesLocally($userMessage);
        }
        $payload = json_encode(['services' => $services], JSON_UNESCAPED_UNICODE);
        return __('chatbot.booking_suggestion_prefix') . "\n" . 'SUGGEST:' . $payload;
    }

    private function generateFaqAnswer(string $userMessage): string
    {
        $faqs = [
            __('chatbot.faq_how_to_book_q') => __('chatbot.faq_how_to_book_a'),
            __('chatbot.faq_cancel_q') => __('chatbot.faq_cancel_a'),
            __('chatbot.faq_payment_q') => __('chatbot.faq_payment_a'),
        ];
        $m = mb_strtolower($userMessage);
        foreach ($faqs as $k => $v) {
            if (str_contains($m, $k)) {
                return $v;
            }
        }
        return __('chatbot.faq_default');
    }

    /**
     * Call Gemini to get suggestions in fixed JSON schema, validated against local catalog.
     */
    private function suggestServicesWithGemini(string $userMessage): ?array
    {
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            return null;
        }
        try {
            $prompt = 'You are a booking assistant for a beauty clinic. Given the user message, return ONLY a JSON object with this exact shape: {"services":[{"service_id":number,"name":string}]}. Rules: Suggest 1-5 services most relevant; service_id and name must exist in our catalog. User message: "' . $userMessage . '"';
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent', [
                'contents' => [[
                    'parts' => [['text' => $prompt]],
                ]],
            ]);
            if (!$response->successful()) {
                return null;
            }
            $raw = $response->json();
            $candidateText = $raw['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if (!$candidateText) {
                return null;
            }
            $jsonStart = strpos($candidateText, '{');
            $jsonEnd = strrpos($candidateText, '}');
            if ($jsonStart === false || $jsonEnd === false || $jsonEnd <= $jsonStart) {
                return null;
            }
            $jsonString = substr($candidateText, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonString, true);
            if (!is_array($decoded) || !isset($decoded['services']) || !is_array($decoded['services'])) {
                return null;
            }
            $validated = [];
            $ids = collect($decoded['services'])->pluck('service_id')->filter()->unique()->take(5)->values();
            $existing = ServiceModel::whereIn('id', $ids)->get(['id', 'name'])->keyBy('id');
            foreach ($decoded['services'] as $svc) {
                $sid = (int)($svc['service_id'] ?? 0);
                if ($sid && isset($existing[$sid])) {
                    $validated[] = [
                        'service_id' => $sid,
                        'name' => (string)$existing[$sid]->name,
                    ];
                }
                if (count($validated) >= 5) break;
            }
            return $validated ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Fallback: local keyword search on Service model.
     */
    private function suggestServicesLocally(string $userMessage): array
    {
        $q = trim($userMessage);
        $query = ServiceModel::query();
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            });
        }
        $services = $query->limit(5)->get(['id', 'name']);
        if ($services->isEmpty()) {
            $services = ServiceModel::limit(5)->get(['id', 'name']);
        }
        return $services->map(fn ($s) => ['service_id' => $s->id, 'name' => $s->name])->all();
    }
}