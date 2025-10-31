<?php

namespace App\Services;

use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\Contracts\StaffRepositoryInterface;
use App\Services\Contracts\ChatbotServiceInterface;
use App\Models\Branch;
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
        private readonly ServiceRepositoryInterface $serviceRepository,
        private readonly StaffRepositoryInterface $staffRepository,
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
                    'message' => trim($responseText),
                ]);

                // Update session activity
                $session->update(['last_activity' => Carbon::now()]);
            } catch (\Exception $e) {
                Log::warning('Failed to persist chat messages', ['error' => $e->getMessage()]);
            }

            // Extract inline IDs from assistant message and resolve entity details
            $ids = $this->extractInlineEntityIds($responseText);
            $entities = $this->resolveEntitiesForIds($ids['service_ids'], $ids['branch_ids']);

            return [
                'message' => trim($responseText),
                'user_id' => $userId,
                'locale' => $locale,
                'session_key' => $session->session_key,
                'entities' => $entities,
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
     * Extract inline entity IDs like [service_id: 1] and [branch_id: 10] from a text.
     * Returns [service_ids => int[], branch_ids => int[]].
     */
    public function extractInlineEntityIds(string $text): array
    {
        $serviceIds = [];
        $branchIds = [];

        if (preg_match_all('/\[\s*service_id\s*:\s*(\d+)\s*\]/i', $text, $m1)) {
            $serviceIds = array_map('intval', $m1[1]);
        }
        if (preg_match_all('/\[\s*branch_id\s*:\s*(\d+)\s*\]/i', $text, $m2)) {
            $branchIds = array_map('intval', $m2[1]);
        }

        // De-duplicate
        $serviceIds = array_values(array_unique($serviceIds));
        $branchIds = array_values(array_unique($branchIds));

        return [
            'service_ids' => $serviceIds,
            'branch_ids' => $branchIds,
        ];
    }

    /**
     * Resolve entities for given IDs using repositories. Optionally compute available slots
     * when both $date (YYYY-MM-DD) and $time (HH:MM) are provided.
     * Returns array with keys: services, branches, staff, available_slots.
     */
    public function resolveEntitiesForIds(array $serviceIds, array $branchIds, ?string $date = null, ?string $time = null): array
    {
        // Fetch services
        $services = [];
        if (!empty($serviceIds)) {
            $services = $this->serviceRepository->all()
                ->whereIn('id', $serviceIds)
                ->values()
                ->map(fn ($s) => $s->toArray())
                ->all();
        }

        // Fetch branches
        $branches = [];
        if (!empty($branchIds)) {
            $branches = $this->branchRepository->all()
                ->whereIn('id', $branchIds)
                ->values()
                ->map(fn ($b) => $b->toArray())
                ->all();
        }

        // Fetch staff only when exactly one branch is specified
        $staff = [];
        if (count($branchIds) === 1) {
            $bid = (int) $branchIds[0];
            $this->staffRepository->getForBranch($bid)->each(function ($s) use (&$staff) {
                $staff[$s->id] = $s->toArray();
            });
            $staff = array_values($staff);
        }

        // Available slots per (branch_id, service_id) if date & time provided
        $availableSlots = [];
        if (!empty($date) && !empty($time)) {
            foreach ($branchIds as $bid) {
                foreach ($serviceIds as $sid) {
                    $availableStaff = $this->staffRepository->getAvailableForBooking((int) $bid, (int) $sid, $date, $time);
                    $availableSlots[] = [
                        'branch_id' => (int) $bid,
                        'service_id' => (int) $sid,
                        'date' => $date,
                        'time' => $time,
                        'staff_ids' => $availableStaff->pluck('id')->values()->all(),
                    ];
                }
            }
        } else {
            // If no explicit date/time: compute for today and tomorrow based on branch opening hours.
            $dates = [
                Carbon::today(),
                Carbon::today()->copy()->addDay(),
            ];
            // Collect unique times per date (no IDs in output)
            $timesPerDate = [];
            foreach ($dates as $cDate) {
                $timesPerDate[$cDate->toDateString()] = collect();
            }

            foreach ($branchIds as $bid) {
                $branch = Branch::find((int) $bid);
                if (!$branch || empty($branch->opening_hours) || !is_array($branch->opening_hours)) {
                    continue;
                }
                foreach ($dates as $cDate) {
                    $weekday = strtolower($cDate->englishDayOfWeek); // monday ... sunday
                    if (!isset($branch->opening_hours[$weekday]) || !is_array($branch->opening_hours[$weekday]) || count($branch->opening_hours[$weekday]) !== 2) {
                        continue;
                    }
                    [$open, $close] = $branch->opening_hours[$weekday];
                    // Build time slots at 60-minute intervals
                    $start = Carbon::parse($cDate->toDateString() . ' ' . $open);
                    $end = Carbon::parse($cDate->toDateString() . ' ' . $close);
                    for ($t = $start->copy(); $t->lt($end); $t->addMinutes(60)) {
                        $tStr = $t->format('H:i');
                        // If any service/branch has available staff at this time, include it
                        foreach ($serviceIds as $sid) {
                            $availableStaff = $this->staffRepository->getAvailableForBooking((int) $bid, (int) $sid, $cDate->toDateString(), $tStr);
                            if ($availableStaff->isNotEmpty()) {
                                $timesPerDate[$cDate->toDateString()]->push($tStr);
                                break;
                            }
                        }
                    }
                }
            }

            // Normalize: unique and sort times, cap to 6 per day
            $availableSlots = [];
            foreach ($timesPerDate as $dateKey => $times) {
                $uniqueTimes = collect($times)->unique()->sort()->values()->take(6)->all();
                $availableSlots[] = [
                    'date' => $dateKey,
                    'times' => $uniqueTimes,
                ];
            }
        }

        return [
            'service' => $services,
            'branch' => $branches,
            'staff' => $staff,
            'available_slots' => $availableSlots,
        ];
    }

    /**
     * Convenience method: parse inline IDs from a message and resolve details via repositories.
     * - Extracts [service_id: X] and [branch_id: Y] from $message
     * - Returns services, branches, staff (for branches), and available_slots (if date & time provided)
     */
    public function getEntityDetailsFromMessage(string $message, ?string $date = null, ?string $time = null): array
    {
        $ids = $this->extractInlineEntityIds($message);
        return $this->resolveEntitiesForIds($ids['service_ids'], $ids['branch_ids'], $date, $time);
    }

    
}
