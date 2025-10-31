<?php

namespace App\Services;

use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Services\Contracts\ChatbotServiceInterface;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

/**
 * Service for handling chatbot interactions with AI.
 *
 * Manages chat sessions, processes messages, and integrates with external AI services.
 */
class ChatbotService implements ChatbotServiceInterface
{
    /**
     * Create a new ChatbotService instance.
     *
     * @param BranchRepositoryInterface $branchRepository The branch repository
     * @param ServiceRepositoryInterface $serviceRepository The service repository
     */
    public function __construct(
        private readonly BranchRepositoryInterface $branchRepository,
        private readonly ServiceRepositoryInterface $serviceRepository
    ) {
    }

    /**
     * Process chat message and generate response.
     *
     * @param string $message User's message
     * @param string $locale Language (vi or en)
     * @param int|null $userId User ID if authenticated
     * @return array Response data
     * @throws \Exception
     */
    public function chat(string $message, string $locale = 'vi', ?int $userId = null, ?string $sessionKey = null): array
    {
        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            throw new \Exception(__('chatbot.api_key_missing'));
        }

    // Resolve chat session (user or guest with sessionKey)
    $session = $this->resolveSession($userId, $sessionKey);

    // Get last conversation messages (5 pairs = 10 messages)
    $lastMessages = $session->messages()->take(14)->get()->reverse()->values();

    // Build conversation history string
    $conversationHistory = $this->buildConversationContents($lastMessages, $locale);

    // Get context for the chatbot
    $context = $this->getContext($locale);

        // Prepare system instruction
        $systemInstruction = $this->getSystemInstruction($locale);

        // Prepare the full prompt (include conversation history to provide memory)
        $fullPrompt = $systemInstruction . "\n\n";
        if (!empty($conversationHistory)) {
            $fullPrompt .= "Conversation history:\n" . $conversationHistory . "\n\n";
        }
        $fullPrompt .= $context . "\n\nUser: " . $message;

        try {
            // Call Gemini API
            $geminiConfig = config('chatbot.gemini');
            $response = Http::timeout($geminiConfig['timeout'])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $apiKey,
                ])
                ->post($geminiConfig['api_url'], [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $fullPrompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => $geminiConfig['generation_config']['temperature'],
                        'topK' => $geminiConfig['generation_config']['top_k'],
                        'topP' => $geminiConfig['generation_config']['top_p'],
                        'maxOutputTokens' => $geminiConfig['generation_config']['max_output_tokens'],
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception(__('chatbot.api_error'));
            }

            $data = $response->json();

            // Extract response text
            $responseText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($responseText)) {
                throw new \Exception(__('chatbot.no_response'));
            }

            // Extract structured JSON (service/branch) and clean message text
            [$cleanMessageText, $structuredPayload] = $this->extractStructuredJson($responseText);

            // Persist the user message and assistant reply to DB
            try {
                // Save user message
                ChatMessage::create([
                    'chat_session_id' => $session->id,
                    'user_id' => $userId,
                    'role' => 'user',
                    'message' => $message,
                ]);

                // Save assistant message
                ChatMessage::create([
                    'chat_session_id' => $session->id,
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'message' => trim($cleanMessageText),
                ]);

                // Update session activity
                $session->update(['last_activity' => Carbon::now()]);
            } catch (\Exception $e) {
                Log::warning('Failed to persist chat messages', ['error' => $e->getMessage()]);
            }

            return [
                'message' => trim($cleanMessageText),
                'user_id' => $userId,
                'locale' => $locale,
                'session_key' => $session->session_key,
                'structured' => $structuredPayload,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini API connection error', ['error' => $e->getMessage()]);
            throw new \Exception(__('chatbot.connection_error'));
        } catch (\Exception $e) {
            Log::error('Chatbot error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get conversation context for the chatbot.
     *
     * @param string $locale Language code (vi, en, etc.)
     * @return string Context string
     */
    public function getContext(string $locale = 'vi'): string
    {
        // Get business information from config
        $businessInfo = config('chatbot.business');
        
        // Get branches using existing repository
        $branches = $this->branchRepository->getActive();
        
        // Get services using existing repository
        $services = $this->serviceRepository->all()
            ->where('is_active', true)
            ->sortBy('display_order');

        // Build context string using translations
        $context = "=== " . __('chatbot.context.business_info', [], $locale) . " ===\n";
        $context .= __('chatbot.context.business_name', [], $locale) . ': ' . ($businessInfo['name'][$locale] ?? $businessInfo['name']['vi']) . "\n";
        $context .= __('chatbot.context.description', [], $locale) . ': ' . ($businessInfo['description'][$locale] ?? $businessInfo['description']['vi']) . "\n";
        $context .= __('chatbot.context.specialties', [], $locale) . ': ' . ($businessInfo['specialties'][$locale] ?? $businessInfo['specialties']['vi']) . "\n";
        $context .= "Email: " . $businessInfo['email'] . "\n";
        $context .= __('chatbot.context.phone', [], $locale) . ': ' . $businessInfo['phone'] . "\n";
        $context .= "Hotline: " . $businessInfo['hotline'] . "\n\n";

        // Branches with their specific working hours (include IDs and slugs to help entity resolution)
        $context .= "=== " . __('chatbot.context.branches', [], $locale) . " ===\n";
        foreach ($branches as $branch) {
            $branchName = is_array($branch->name) ? ($branch->name[$locale] ?? $branch->name['vi']) : $branch->name;
            $branchAddress = is_array($branch->address) ? ($branch->address[$locale] ?? $branch->address['vi']) : $branch->address;
            
            // Format working hours from branch's opening_hours field
            $branchHours = 'N/A';
            if ($branch->opening_hours && is_array($branch->opening_hours)) {
                $branchHours = $this->formatWorkingHours($branch->opening_hours, $locale);
            }
            
            $context .= "- [id: {$branch->id}] {$branchName} (slug: {$branch->slug})\n";
            $context .= "  " . __('chatbot.context.address', [], $locale) . ': ' . $branchAddress . "\n";
            $context .= "  " . __('chatbot.context.phone', [], $locale) . ': ' . ($branch->phone ?? 'N/A') . "\n";
            $context .= "  " . __('chatbot.context.working_hours', [], $locale) . ":\n" . $branchHours . "\n";
        }

        // Services (include IDs and slugs to help entity resolution)
        $context .= "=== " . __('chatbot.context.services', [], $locale) . " ===\n";
        foreach ($services as $service) {
            $serviceName = is_array($service->name) ? ($service->name[$locale] ?? $service->name['vi']) : $service->name;
            $serviceDesc = is_array($service->description) ? ($service->description[$locale] ?? $service->description['vi']) : $service->description;
            
            $context .= "- [id: {$service->id}] {$serviceName} (slug: {$service->slug})\n";
            if ($serviceDesc) {
                $context .= "  " . __('chatbot.context.description', [], $locale) . ': ' . $serviceDesc . "\n";
            }
            $context .= "  " . __('chatbot.context.price', [], $locale) . ': ' . number_format($service->price, 0, ',', '.') . " VNĐ\n";
            $context .= "  " . __('chatbot.context.duration', [], $locale) . ': ' . $service->duration . " " . __('chatbot.context.minutes', [], $locale) . "\n";
            if ($service->category) {
                $categoryName = is_array($service->category->name) ? ($service->category->name[$locale] ?? $service->category->name['vi']) : $service->category->name;
                $context .= "  " . __('chatbot.context.category', [], $locale) . ': ' . $categoryName . "\n";
            }
            $context .= "\n";
        }

        return $context;
    }

    /**
     * Get system instruction for the chatbot.
     *
     * @param string $locale Language code (vi, en, etc.)
     * @return string System instruction
     */
    private function getSystemInstruction(string $locale = 'vi'): string
    {
        return config("chatbot.system_instructions.{$locale}");
    }

    /**
     * Format working hours array into readable string.
     *
     * @param array $openingHours Opening hours data
     * @param string $locale Language code (vi, en, etc.)
     * @return string Formatted working hours
     */
    private function formatWorkingHours(array $openingHours, string $locale = 'vi'): string
    {
        $formatted = [];
        foreach ($openingHours as $day => $hours) {
            $dayName = __("chatbot.days.{$day}", [], $locale);
            if (is_array($hours) && count($hours) === 2) {
                $formatted[] = "    {$dayName}: {$hours[0]} - {$hours[1]}";
            }
        }

        return implode("\n", $formatted);
    }

    /**
     * Resolve chat session for a user or a guest session key. If session does not exist, create one.
     *
     * @param int|null $userId
     * @param string|null $sessionKey
     * @return \App\Models\ChatSession
     */
    private function resolveSession(?int $userId, ?string $sessionKey)
    {
        // If user authenticated, prefer user-linked session
        if ($userId) {
            $session = ChatSession::firstOrCreate(
                ['user_id' => $userId],
                ['session_key' => (string) Str::uuid()]
            );
            return $session;
        }

        // Guest: try to find by session_key
        if ($sessionKey) {
            $session = ChatSession::where('session_key', $sessionKey)->first();
            if ($session) {
                return $session;
            }
        }

        // Create a new guest session with generated key
        $newKey = (string) Str::uuid();
        return ChatSession::create(['session_key' => $newKey]);
    }

    /**
     * Build a conversation string from a collection of ChatMessage models.
     * Keeps chronological order (oldest first).
     *
     * @param \Illuminate\Support\Collection $messages
     * @param string $locale
     * @return string
     */
    private function buildConversationContents($messages, string $locale = 'vi'): string
    {
        $parts = [];
        foreach ($messages as $m) {
            $role = $m->role === 'assistant' ? 'Assistant' : 'User';
            // Optionally localize role labels in future
            $parts[] = $role . ': ' . trim($m->message);
        }

        return implode("\n", $parts);
    }

    /**
     * Extract a minimal JSON block containing service/branch arrays from model text.
     * Returns [cleanText, structuredPayload].
     */
    private function extractStructuredJson(string $text): array
    {
        $cleanText = $text;
        $structured = [
            'service' => [],
            'branch' => [],
        ];

        // 1) Try fenced ```json code block
        if (preg_match('/```json\s*([\s\S]*?)\s*```/i', $text, $m)) {
            $jsonStr = trim($m[1]);
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $structured = $this->normalizeStructured($decoded);
                // remove code block from message
                $cleanText = trim(str_replace($m[0], '', $text));
                return [$cleanText, $structured];
            }
        }

        // 2) Try to find trailing JSON object
        if (preg_match('/(\{[\s\S]*\})\s*$/', $text, $m2)) {
            $jsonStr = trim($m2[1]);
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $structured = $this->normalizeStructured($decoded);
                $cleanText = trim(str_replace($m2[1], '', $text));
                return [$cleanText, $structured];
            }
        }

        // 3) Nothing parsed; return text as-is
        return [$cleanText, $structured];
    }

    /**
     * Ensure structure contains service[] and branch[] arrays with required id/slug keys if present.
     */
    private function normalizeStructured(array $input): array
    {
        $out = [
            'service' => [],
            'branch' => [],
        ];

        $services = $input['service'] ?? [];
        $branches = $input['branch'] ?? [];
        if (!is_array($services)) { $services = []; }
        if (!is_array($branches)) { $branches = []; }

        // Coerce single objects to arrays
        if ($services && array_keys($services) !== range(0, count($services) - 1)) {
            $services = [$services];
        }
        if ($branches && array_keys($branches) !== range(0, count($branches) - 1)) {
            $branches = [$branches];
        }

        // Filter invalid entries without id
        $services = array_values(array_filter($services, function ($s) {
            return is_array($s) && array_key_exists('id', $s) && $s['id'] !== null;
        }));
        $branches = array_values(array_filter($branches, function ($b) {
            return is_array($b) && array_key_exists('id', $b) && $b['id'] !== null;
        }));

        $out['service'] = $services;
        $out['branch'] = $branches;
        return $out;
    }
}
