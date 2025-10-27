<?php

namespace App\Repositories\Eloquent;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ChatRepository extends BaseRepository implements ChatRepositoryInterface
{
    public function __construct(ChatSession $model)
    {
        parent::__construct($model);
    }

    public function getUserSessions(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getSessionById(int $id): ?ChatSession
    {
        return $this->model->find($id);
    }

    public function createSession(int $userId, string $title = null): ChatSession
    {
        return $this->model->create([
            'user_id' => $userId,
            'title' => $title ?? 'New Chat Session',
            'status' => 'active'
        ]);
    }

    public function getSessionMessages(int $sessionId): Collection
    {
        return ChatMessage::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function sendMessage(int $sessionId, string $content, string $type = 'user'): ChatMessage
    {
        $message = ChatMessage::create([
            'session_id' => $sessionId,
            'content' => $content,
            'type' => $type
        ]);

        // Update session's last activity
        $this->model->where('id', $sessionId)->update(['updated_at' => now()]);

        return $message;
    }

    public function deleteSession(ChatSession $session): bool
    {
        // Delete all messages first
        ChatMessage::where('session_id', $session->id)->delete();
        
        return $session->delete();
    }

    public function clearSessionMessages(ChatSession $session): bool
    {
        return ChatMessage::where('session_id', $session->id)->delete() > 0;
    }
}

