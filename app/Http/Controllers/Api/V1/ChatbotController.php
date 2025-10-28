<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\SendMessageRequest;
use App\Http\Requests\Chatbot\CreateSessionRequest;
use App\Http\Resources\Chatbot\ChatSessionResource;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Services\Contracts\ChatbotServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    /**
     * Create a new ChatbotController instance.
     *
     * @param ChatbotServiceInterface $service The chatbot service
     */
    public function __construct(private readonly ChatbotServiceInterface $service)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chatbot/sessions",
     *     summary="List chat sessions",
     *     tags={"Chatbot"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display a listing of chat sessions.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse The list of chat sessions
     */
    public function sessions(Request $request): JsonResponse
    {
        
        $sessions = $this->service->getUserSessions($request->user()->id);
        $items = $sessions->map(fn ($model) => ChatSessionResource::make($model));
        
        return $this->ok($items, 'Chat sessions retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chatbot/sessions",
     *     summary="Create chat session",
     *     tags={"Chatbot"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Store a newly created chat session.
     *
     * @param CreateSessionRequest $request The create session request
     * @return JsonResponse The created chat session response
     */
    public function createSession(CreateSessionRequest $request): JsonResponse
    {
        $session = $this->service->createSession($request->user()->id, $request->title);
        return $this->created(ChatSessionResource::make($session), 'Chat session created successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chatbot/sessions/{id}",
     *     summary="Get chat session by id",
     *     tags={"Chatbot"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Display the specified chat session.
     *
     * @param int $id The chat session ID
     * @return JsonResponse The chat session response
     */
    public function showSession(int $id): JsonResponse
    {
        $session = $this->service->getSessionById($id);
        if (!$session) {
            $this->notFound('Chat session');
        }
        
        
        return $this->ok(ChatSessionResource::make($session), 'Chat session retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/chatbot/sessions/{id}/messages",
     *     summary="Get chat messages for session",
     *     tags={"Chatbot"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Get chat history for a session.
     *
     * @param int $id The chat session ID
     * @return JsonResponse The chat messages response
     */
    public function getHistory(int $id): JsonResponse
    {
        $session = $this->service->getSessionById($id);
        if (!$session) {
            $this->notFound('Chat session');
        }
        
        
        $messages = $this->service->getSessionMessages($id);
        $items = $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'content' => $message->content,
                'type' => $message->type,
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
            ];
        });
        
        return $this->ok($items, 'Chat messages retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/chatbot/sessions/{id}/messages",
     *     summary="Send message to chatbot",
     *     tags={"Chatbot"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *     @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Send a message to the chatbot.
     *
     * @param SendMessageRequest $request The send message request
     * @param int $id The chat session ID
     * @return JsonResponse The message response
     */
    public function sendMessage(SendMessageRequest $request, int $id): JsonResponse
    {
        $session = $this->service->getSessionById($id);
        if (!$session) {
            $this->notFound('Chat session');
        }
        
        
        $response = $this->service->processBotResponse($id, $request->message);
        
        $messageData = [
            'id' => $response->id,
            'content' => $response->content,
            'type' => $response->type,
            'created_at' => $response->created_at,
            'updated_at' => $response->updated_at,
        ];
        
        return $this->ok($messageData, 'Message sent successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/chatbot/sessions/{id}",
     *     summary="Delete chat session",
     *     tags={"Chatbot"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Remove the specified chat session from storage.
     *
     * @param int $id The chat session ID
     * @return JsonResponse The deletion response
     */
    public function destroySession(int $id): JsonResponse
    {
        $session = $this->service->getSessionById($id);
        if (!$session) {
            $this->notFound('Chat session');
        }
        
        
        $deleted = $this->service->deleteSession($id);
        return $this->noContent('Chat session deleted successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/chatbot/sessions/{id}/messages",
     *     summary="Clear chat session messages",
     *     tags={"Chatbot"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * Clear messages for the specified chat session.
     *
     * @param int $id The chat session ID
     * @return JsonResponse The clear response
     */
    public function clearMessages(int $id): JsonResponse
    {
        $session = $this->service->getSessionById($id);
        if (!$session) {
            $this->notFound('Chat session');
        }
        
        
        $cleared = $this->service->clearSessionMessages($id);
        return $this->noContent('Chat messages cleared successfully');
    }
}
