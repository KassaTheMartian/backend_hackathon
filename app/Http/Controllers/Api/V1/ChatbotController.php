<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\ChatRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Contracts\ChatbotServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\ChatSession;
use App\Http\Resources\Chat\ChatMessageResource;

class ChatbotController extends Controller
{
    /**
     * Create a new ChatbotController instance.
     *
     * @param ChatbotServiceInterface $chatbotService The chatbot service
     */
    public function __construct(private readonly ChatbotServiceInterface $chatbotService)
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chatbot",
     *     summary="Chat with AI assistant",
     *     description="Send a message to the AI chatbot and receive a response. Available for both authenticated and guest users.",
     *     tags={"Chatbot"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", minLength=1, maxLength=1000, example="Tôi muốn biết thông tin về dịch vụ chăm sóc da"),
     *             @OA\Property(property="session_key", type="string", example="b7f3a9e2-...", description="Optional client-side session key for guest users (store in localStorage/cookie)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200, 
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Response generated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Chúng tôi cung cấp các dịch vụ chăm sóc da..."),
     *                 @OA\Property(property="user_id", type="integer", nullable=true, example=1),
     *                 @OA\Property(property="locale", type="string", example="vi")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422, 
     *         description="Validation Error", 
     *         @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")
     *     ),
     *     @OA\Response(
     *         response=500, 
     *         description="Server Error", 
     *         @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")
     *     )
     * )
     * 
     * Process chat message and return AI response.
     *
     * @param ChatRequest $request The chat request
     * @return JsonResponse The chatbot response
     */
    public function chat(ChatRequest $request): JsonResponse
    {
        try {
            // Get locale from app (already set by SetLocale middleware)
            $locale = app()->getLocale();

            // Get user ID if authenticated
            $userId = $this->user()?->id;

            // session_key: optional for guest clients (can be sent in body or header X-Chat-Session)
            $sessionKey = $request->input('session_key') ?? $request->header('X-Chat-Session');

            // Process chat message (pass sessionKey so service can load/create session)
            $response = $this->chatbotService->chat(
                message: $request->message,
                locale: $locale,
                userId: $userId,
                sessionKey: $sessionKey
            );

            return $this->ok($response, __('chatbot.response_success'));

        } catch (\Exception $e) {
            return ApiResponse::error(
                $e->getMessage(),
                __('chatbot.response_failed'),
                'CHATBOT_ERROR',
                500
            );
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chatbot/history",
     *     summary="Get AI chatbot conversation history",
     *     description="Return messages for the current authenticated user or a guest identified by session_key.",
     *     tags={"Chatbot"},
     *     @OA\Parameter(name="session_key", in="query", required=false, @OA\Schema(type="string"), description="Guest session key (if not authenticated)"),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     *
     * Get chatbot conversation history.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $this->user();
        $session = null;

        if ($user) {
            $session = ChatSession::where('user_id', $user->id)->first();
        } else {
            $sessionKey = $request->query('session_key') ?? $request->header('X-Chat-Session');
            if ($sessionKey) {
                $session = ChatSession::where('session_key', $sessionKey)->first();
            }
        }

        if (!$session) {
            return $this->ok(['messages' => []], __('chatbot.response_success'));
        }

        $messages = $session->messages()->orderBy('id')->get();
        return $this->ok([
            'session_key' => $session->session_key,
            'messages' => ChatMessageResource::collection($messages),
        ], __('chatbot.response_success'));
    }
}
