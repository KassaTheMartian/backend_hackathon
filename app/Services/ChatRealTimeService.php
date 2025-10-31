<?php

namespace App\Services;

use App\Data\Chat\ChatSessionData;
use App\Data\Chat\ChatMessageData;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Services\Contracts\ChatRealTimeServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class ChatRealTimeService implements ChatRealTimeServiceInterface
{
    /** Create or get a guest chat session by session_key. */
    public function createGuestSession(ChatSessionData $data): ChatSession
    {
        $session = ChatSession::firstOrCreate(
            ['session_key' => $data->session_key],
            [
                'user_id' => $data->user_id,
                'meta' => [
                    'guest_name' => $data->guest_name,
                    'guest_email' => $data->guest_email,
                    'guest_phone' => $data->guest_phone,
                ],
                'is_active' => true,
            ],
        );

        // Merge new guest info if provided and bump activity
        $newMeta = array_filter([
            'guest_name' => $data->guest_name,
            'guest_email' => $data->guest_email,
            'guest_phone' => $data->guest_phone,
        ], fn($v) => !is_null($v));
        if (!empty($newMeta)) {
            $session->meta = array_merge($session->meta ?? [], $newMeta);
        }
        $session->last_activity = now();
        $session->save();

        return $session;
    }

    /** Get guest chat history by session_key. */
    public function getGuestHistory(string $sessionKey): ?ChatSession
    {
        return ChatSession::where('session_key', $sessionKey)->first();
    }

    /** Persist a guest message using session_key in DTO. */
    public function guestSendMessage(ChatMessageData $data): ChatMessage
    {
        $session = ChatSession::where('session_key', $data->session_key)->firstOrFail();

        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'user_id' => $data->user_id,
            'role' => $data->role,
            'message' => $data->message,
            'meta' => $data->meta,
        ]);

        $session->last_activity = now();
        $session->save();

        return $message;
    }

    // Staff/transfer features removed in this schema

    // Staff messaging removed

    /** Get new messages by session_key for polling. */
    public function getNewMessages(string $sessionKey, int $lastMessageId = 0): Collection
    {
        $session = ChatSession::where('session_key', $sessionKey)->firstOrFail();
        return ChatMessage::where('chat_session_id', $session->id)
            ->when($lastMessageId > 0, fn($q) => $q->where('id', '>', $lastMessageId))
            ->orderBy('id')
            ->get();
    }

    // Admin functions

    public function getAdminSessions(int $adminUserId, string $filter = 'unassigned'): Collection
    {
        $query = ChatSession::query()
            ->where('is_active', true)
            ->orderByDesc('last_activity');

        if ($filter === 'mine') {
            $query->where('assigned_user_id', $adminUserId);
        } elseif ($filter === 'unassigned') {
            $query->whereNull('assigned_user_id');
        }

        return $query->get();
    }

    public function assignSession(string $sessionKey, int $adminUserId): ChatSession
    {
        $session = ChatSession::where('session_key', $sessionKey)->firstOrFail();
        if ($session->assigned_user_id && $session->assigned_user_id !== $adminUserId) {
            // Already assigned to someone else; keep it as-is but still return
            return $session;
        }
        $session->assigned_user_id = $adminUserId;
        $session->assigned_at = now();
        $session->last_activity = now();
        $session->save();
        return $session;
    }

    public function adminSendMessage(ChatMessageData $data, int $adminUserId): ChatMessage
    {
        $session = ChatSession::where('session_key', $data->session_key)->firstOrFail();

        // Auto-assign if unassigned
        if (!$session->assigned_user_id) {
            $session->assigned_user_id = $adminUserId;
            $session->assigned_at = now();
        }

        // Only allow sender if assigned to this admin
        if ($session->assigned_user_id !== $adminUserId) {
            throw new \Exception(__('chat_realtime.not_assigned'));
        }

        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'user_id' => $adminUserId,
            'role' => 'assistant',
            'message' => $data->message,
            'meta' => $data->meta,
        ]);

        $session->last_activity = now();
        $session->save();

        return $message;
    }

    public function getAdminSessionMessages(string $sessionKey, int $lastMessageId = 0): Collection
    {
        $session = ChatSession::where('session_key', $sessionKey)->firstOrFail();
        return ChatMessage::where('chat_session_id', $session->id)
            ->when($lastMessageId > 0, fn($q) => $q->where('id', '>', $lastMessageId))
            ->orderBy('id')
            ->get();
    }
}
