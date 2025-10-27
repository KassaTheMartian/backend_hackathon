<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\SendMessageRequest;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbotService
    ) {}

    /**
     * Send a message to the chatbot.
     */
    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        $response = $this->chatbotService->processMessage($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $response,
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get chat history for a session.
     */
    public function getHistory(Request $request, string $sessionId): JsonResponse
    {
        $history = $this->chatbotService->getChatHistory($sessionId);
        
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $history,
            'error' => null,
            'meta' => null,
            'trace_id' => $request->header('X-Trace-ID'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}