<?php

namespace App\Services;

use App\Data\Chat\ChatSessionData;
use App\Data\Chat\ChatMessageData;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Staff;
use App\Services\Contracts\ChatRealTimeServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class ChatRealTimeService implements ChatRealTimeServiceInterface
{
    /**
     * Create a guest chat session.
     */
    public function createGuestSession(ChatSessionData $data): ChatSession
    {
        // Check if session already exists
        $session = ChatSession::where('session_id', $data->session_id)->first();
        
        if (!$session) {
            $session = ChatSession::create([
                'session_id' => $data->session_id,
                'user_id' => $data->user_id,
                'guest_name' => $data->guest_name,
                'guest_email' => $data->guest_email,
                'status' => $data->status,
                'assigned_to' => $data->assigned_to,
                'started_at' => now(),
                'metadata' => $data->metadata,
            ]);
        }
        
        return $session;
    }

    /**
     * Get guest chat history.
     */
    public function getGuestHistory(string $sessionId): ?ChatSession
    {
        return ChatSession::where('session_id', $sessionId)->first();
    }

    /**
     * Send message as guest.
     */
    public function guestSendMessage(ChatMessageData $data): ChatMessage
    {
        return ChatMessage::create([
            'session_id' => $data->session_id,
            'sender_type' => $data->sender_type,
            'sender_id' => $data->sender_id,
            'message' => $data->message,
            'message_type' => $data->message_type,
            'is_bot' => $data->is_bot,
            'bot_confidence' => $data->bot_confidence,
            'metadata' => $data->metadata,
        ]);
    }

    /**
     * Transfer chat to human staff.
     */
    public function transferToHuman(int $sessionId): array
    {
        $session = ChatSession::findOrFail($sessionId);
        
        // Find available staff (simple random selection)
        $availableStaff = Staff::active()
            ->whereDoesntHave('chatSessions', function($q) {
                $q->where('status', 'active');
            })
            ->inRandomOrder()
            ->first();
        
        if (!$availableStaff) {
            return ['success' => false, 'message' => __('chat_realtime.no_staff_available')];
        }
        
        // Update session
        $session->update([
            'assigned_to' => $availableStaff->id,
            'status' => 'transferred'
        ]);
        
        // Create notification message
        ChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'staff',
            'sender_id' => $availableStaff->user_id,
            'message' => __('chat_realtime.staff_joined', ['name' => $availableStaff->name]),
            'is_bot' => false,
        ]);
        
        return [
            'success' => true,
            'staff' => $availableStaff,
            'message' => __('chat_realtime.transferred_to_staff')
        ];
    }

    /**
     * Send message as staff.
     */
    public function staffSendMessage(ChatMessageData $data, int $staffUserId): ChatMessage
    {
        $session = ChatSession::findOrFail($data->session_id);
        $staff = Staff::where('user_id', $staffUserId)->firstOrFail();
        
        // Check permission
        if ($session->assigned_to !== $staff->id) {
            throw new \Exception(__('chat_realtime.not_assigned'));
        }
        
        return ChatMessage::create([
            'session_id' => $data->session_id,
            'sender_type' => $data->sender_type,
            'sender_id' => $data->sender_id,
            'message' => $data->message,
            'message_type' => $data->message_type,
            'is_bot' => $data->is_bot,
            'bot_confidence' => $data->bot_confidence,
            'metadata' => $data->metadata,
        ]);
    }

    /**
     * Get new messages for polling.
     */
    public function getNewMessages(int $sessionId, int $lastMessageId = 0): Collection
    {
        return ChatMessage::where('session_id', $sessionId)
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get sessions assigned to staff.
     */
    public function getStaffSessions(int $staffUserId): Collection
    {
        $staff = Staff::where('user_id', $staffUserId)->firstOrFail();
        
        return ChatSession::where('assigned_to', $staff->id)
            ->whereIn('status', ['active', 'transferred']) // Include both active and transferred
            ->with(['messages' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(1);
            }])
            ->orderBy('started_at', 'desc')
            ->get();
    }
}
