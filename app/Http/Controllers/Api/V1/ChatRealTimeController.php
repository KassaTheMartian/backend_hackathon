<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\CreateGuestSessionRequest;
use App\Http\Requests\Chat\UpdateChatSessionRequest;
use App\Http\Resources\Chat\ChatSessionResource;
use App\Http\Resources\Chat\ChatMessageResource;
use App\Services\Contracts\ChatRealTimeServiceInterface;
use App\Data\Chat\ChatSessionData;
use App\Data\Chat\ChatMessageData;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatRealTimeController extends Controller
{
    /**
     * Create a new ChatRealTimeController instance.
     *
     * @param ChatRealTimeServiceInterface $service The chat real-time service
     */
    public function __construct(private readonly ChatRealTimeServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/guest/session",
     *     summary="Create guest chat session",
     *     tags={"Chat Real-time"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"session_id"},
     *             @OA\Property(property="session_id", type="string"),
     *             @OA\Property(property="guest_name", type="string"),
     *             @OA\Property(property="guest_email", type="string"),
     *             @OA\Property(property="guest_phone", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
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
        
        $messages = $session->messages()->orderBy('created_at')->get();
        
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
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
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
        
        $messages = $session->messages()->orderBy('created_at')->get();
        
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
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
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
        $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
        
        $dto = ChatMessageData::fromRequest(array_merge($request->validated(), [
            'session_id' => $session->id,
            'sender_type' => 'user',
            'sender_id' => null,
            'is_bot' => false,
        ]));
        
        $message = $this->service->guestSendMessage($dto);
        
        return $this->ok(ChatMessageResource::make($message), __('chat_realtime.message_sent'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/guest/{sessionId}/transfer-human",
     *     summary="Transfer to human staff",
     *     tags={"Chat Real-time"},
     *     @OA\Parameter(name="sessionId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Transfer chat to human staff.
     *
     * @param Request $request The HTTP request
     * @param string $sessionId The session ID
     * @return JsonResponse The transfer response
     */
    public function transferToHuman(Request $request, string $sessionId): JsonResponse
    {
        $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
        $result = $this->service->transferToHuman($session->id);
        
        if (!$result['success']) {
            return $this->ok(null, $result['message']);
        }
        
        return $this->ok([
            'staff' => $result['staff'],
            'message' => $result['message']
        ], __('chat_realtime.transfer_success'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chat/sessions/{id}/staff-message",
     *     summary="Send message as staff",
     *     tags={"Chat Real-time"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Send message as staff.
     *
     * @param SendMessageRequest $request The send message request
     * @param int $id The session ID
     * @return JsonResponse The message response
     */
    public function staffSendMessage(SendMessageRequest $request, int $id): JsonResponse
    {
        $session = ChatSession::findOrFail($id);
        
        $dto = ChatMessageData::fromRequest(array_merge($request->validated(), [
            'session_id' => $session->id,
            'sender_type' => 'staff',
            'sender_id' => $request->user()->id,
            'is_bot' => false,
        ]));
        
        try {
            $message = $this->service->staffSendMessage($dto, $request->user()->id);
            return $this->ok(ChatMessageResource::make($message), __('chat_realtime.message_sent'));
        } catch (\Exception $e) {
            return $this->forbidden($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chat/guest/{sessionId}/messages",
     *     summary="Get new messages",
     *     tags={"Chat Real-time"},
     *     @OA\Parameter(name="sessionId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="last_message_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
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
        $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
        $lastMessageId = $request->input('last_message_id', 0);
        
        $messages = $this->service->getNewMessages($session->id, $lastMessageId);
        
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
    public function getSessionMessages(Request $request, int $id): JsonResponse
    {
        $session = ChatSession::findOrFail($id);
        $staff = Staff::where('user_id', $request->user()->id)->firstOrFail();
        
        // Check permission
        if ($session->assigned_to !== $staff->id) {
            return $this->forbidden(__('chat_realtime.not_assigned'));
        }
        
        $lastMessageId = $request->input('last_message_id', 0);
        $messages = $this->service->getNewMessages($id, $lastMessageId);
        
        return $this->ok(ChatMessageResource::collection($messages), __('chat_realtime.messages_retrieved'));
    }

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
    public function getStaffSessions(Request $request): JsonResponse
    {
        $sessions = $this->service->getStaffSessions($request->user()->id);
        
        return $this->ok(ChatSessionResource::collection($sessions), __('chat_realtime.sessions_retrieved'));
    }
}
