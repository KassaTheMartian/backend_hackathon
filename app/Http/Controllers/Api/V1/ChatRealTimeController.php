<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\CreateGuestSessionRequest;
use App\Http\Resources\Chat\ChatSessionResource;
use App\Http\Resources\Chat\ChatMessageResource;
use App\Services\Contracts\ChatRealTimeServiceInterface;
use App\Data\Chat\ChatSessionData;
use App\Data\Chat\ChatMessageData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatRealTimeController extends Controller
{
    public function __construct(private readonly ChatRealTimeServiceInterface $service)
    {
    }
    // Controller delegates to service; keep controller thin and declarative

    /**
     * @OA\Post(
     *     path="/api/v1/chat/guest/session",
     *     summary="Create guest chat session",
     *     tags={"Chat Real-time"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"session_key"},
     *             @OA\Property(property="session_key", type="string"),
     *             @OA\Property(property="guest_name", type="string"),
     *             @OA\Property(property="guest_email", type="string"),
     *             @OA\Property(property="guest_phone", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             example={
     *                 "success": true,
     *                 "message": "Chat session created",
     *                 "data": {
     *                     "session": {
     *                         "id": 1,
     *                         "user_id": null,
     *                         "session_key": "guest-session-123",
     *                         "meta": {"guest_name": "Guest A", "guest_email": "guest@example.com"},
     *                         "last_activity": "2025-10-31T11:20:00Z",
     *                         "is_active": true,
     *                         "created_at": "2025-10-31T11:20:00Z",
     *                         "updated_at": "2025-10-31T11:20:00Z"
     *                     },
     *                     "messages": {}
     *                 },
     *                 "trace_id": "abc123",
     *                 "timestamp": "2025-10-31T11:20:00Z"
     *             }
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Create a guest chat session.
     *
     * @param CreateGuestSessionRequest $request The create guest session request
     * @return JsonResponse The created session response
     */
    public function createGuestSession(CreateGuestSessionRequest $request): JsonResponse
    {
        $dto = ChatSessionData::fromRequest($request->validated());
        $session = $this->service->createGuestSession($dto);

        $messages = $session->messages()->orderBy('id')->get();

        return $this->created([
            'session' => ChatSessionResource::make($session),
            'messages' => ChatMessageResource::collection($messages)
        ], __('chat_realtime.session_created'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chat/guest/{sessionId}/history",
     *     summary="Get guest chat history",
     *     tags={"Chat Real-time"},
     *     @OA\Parameter(name="sessionId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             example={
     *                 "success": true,
     *                 "message": "History retrieved",
     *                 "data": {
     *                     "session": {"id": 1, "session_key": "guest-session-123"},
     *                     "messages": {{"id": 10, "message": "Hello", "role": "user"}}
     *                 },
     *                 "trace_id": "abc123",
     *                 "timestamp": "2025-10-31T11:21:02Z"
     *             }
     *         )
     *     )
     * )
     * 
     * Get guest chat history.
     *
     * @param string $sessionId The session ID
     * @return JsonResponse The chat history response
     */
    public function getGuestHistory(string $sessionId): JsonResponse
    {
        $session = $this->service->getGuestHistory($sessionId);
        
        if (!$session) {
            return $this->ok([], __('chat_realtime.no_history'));
        }
        
        $messages = $session->messages()->orderBy('id')->get();
        
        return $this->ok([
            'session' => ChatSessionResource::make($session),
            'messages' => ChatMessageResource::collection($messages)
        ], __('chat_realtime.history_retrieved'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/guest/{sessionId}/message",
     *     summary="Send message as guest",
     *     tags={"Chat Real-time"},
     *     @OA\Parameter(name="sessionId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             example={
     *                 "success": true,
     *                 "message": "Message sent",
     *                 "data": {"id": 10, "chat_session_id": 1, "role": "user", "message": "Hello"},
     *                 "trace_id": "abc123",
     *                 "timestamp": "2025-10-31T11:21:00Z"
     *             }
     *         )
     *     )
     * )
     * 
     * Send message as guest.
     *
     * @param SendMessageRequest $request The send message request
     * @param string $sessionId The session ID
     * @return JsonResponse The message response
     */
    public function guestSendMessage(SendMessageRequest $request, string $sessionId): JsonResponse
    {
        $dto = ChatMessageData::fromRequest([
            'session_key' => $sessionId,
            'user_id' => null,
            'role' => 'user',
            'message' => $request->validated()['message'],
            'meta' => null,
        ]);

        $message = $this->service->guestSendMessage($dto);

        return $this->ok(ChatMessageResource::make($message), __('chat_realtime.message_sent'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chat/guest/{sessionId}/messages",
     *     summary="Get new messages",
     *     tags={"Chat Real-time"},
     *     @OA\Parameter(name="sessionId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="last_message_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             example={
     *                 "success": true,
     *                 "message": "Messages retrieved",
     *                 "data": {{"id": 11, "role": "assistant", "message": "Hi there!"}},
     *                 "trace_id": "abc123",
     *                 "timestamp": "2025-10-31T11:21:02Z"
     *             }
     *         )
     *     )
     * )
     * 
     * Get new messages for polling.
     *
     * @param Request $request The HTTP request
     * @param string $sessionId The session ID
     * @return JsonResponse The messages response
     */
    public function getNewMessages(Request $request, string $sessionId): JsonResponse
    {
        $lastMessageId = (int) $request->input('last_message_id', 0);

        $messages = $this->service->getNewMessages($sessionId, $lastMessageId);
        
        return $this->ok(ChatMessageResource::collection($messages), __('chat_realtime.messages_retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chat/admin/sessions",
     *     summary="List chat sessions for admin",
     *     tags={"Chat Real-time"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="filter", in="query", @OA\Schema(type="string", enum={"unassigned","mine","all"})),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function adminSessions(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'unassigned');
        $sessions = $this->service->getAdminSessions($request->user()->id, $filter);
        return $this->ok(ChatSessionResource::collection($sessions), __('chat_realtime.sessions_retrieved'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/admin/{sessionKey}/assign",
     *     summary="Assign a session to current admin",
     *     tags={"Chat Real-time"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="sessionKey", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function adminAssign(Request $request, string $sessionKey): JsonResponse
    {
        $session = $this->service->assignSession($sessionKey, $request->user()->id);
        return $this->ok(ChatSessionResource::make($session), __('chat_realtime.session_assigned'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/admin/{sessionKey}/message",
     *     summary="Send message as admin",
     *     tags={"Chat Real-time"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="sessionKey", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"message"}, @OA\Property(property="message", type="string"))),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function adminSendMessage(SendMessageRequest $request, string $sessionKey): JsonResponse
    {
        $dto = ChatMessageData::fromRequest([
            'session_key' => $sessionKey,
            'user_id' => $request->user()->id,
            'role' => 'assistant',
            'message' => $request->validated()['message'],
            'meta' => null,
        ]);
        try {
            $message = $this->service->adminSendMessage($dto, $request->user()->id);
        } catch (\Exception $e) {
            return $this->forbidden($e->getMessage());
        }
        return $this->ok(ChatMessageResource::make($message), __('chat_realtime.message_sent'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chat/admin/{sessionKey}/messages",
     *     summary="Get session messages for admin",
     *     tags={"Chat Real-time"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="sessionKey", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="last_message_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function adminSessionMessages(Request $request, string $sessionKey): JsonResponse
    {
        $lastMessageId = (int) $request->input('last_message_id', 0);
        $messages = $this->service->getAdminSessionMessages($sessionKey, $lastMessageId);
        return $this->ok(ChatMessageResource::collection($messages), __('chat_realtime.messages_retrieved'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chat/sessions/{id}/messages",
     *     summary="Get session messages (staff)",
     *     tags={"Chat Real-time"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="last_message_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Get session messages for staff.
     *
     * @param Request $request The HTTP request
     * @param int $id The session ID
     * @return JsonResponse The messages response
     */
    // getSessionMessages removed (not supported by current schema)

    /**
     * @OA\Get(
     *     path="/api/v1/chat/staff/sessions",
     *     summary="Get staff assigned sessions",
     *     tags={"Chat Real-time"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Get sessions assigned to staff.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The sessions response
     */
    // getStaffSessions removed (not supported by current schema)
}
