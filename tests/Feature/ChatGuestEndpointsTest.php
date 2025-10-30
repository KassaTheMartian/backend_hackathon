<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatGuestEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Bind a simple fake service to satisfy controller dependencies
        $this->app->bind(\App\Services\Contracts\ChatRealTimeServiceInterface::class, function () {
            return new class implements \App\Services\Contracts\ChatRealTimeServiceInterface {
                public function createGuestSession(\App\Data\Chat\ChatSessionData $data): \App\Models\ChatSession
                {
                    $session = \App\Models\ChatSession::create([
                        'session_key' => $data->session_id,
                        'guest_name' => $data->guest_name,
                        'guest_email' => $data->guest_email,
                        'guest_phone' => $data->guest_phone,
                        'is_active' => true,
                    ]);
                    return $session;
                }
                public function getGuestHistory(string $sessionId): ?\App\Models\ChatSession
                {
                    return \App\Models\ChatSession::where('session_key', $sessionId)->first();
                }
                public function guestSendMessage(\App\Data\Chat\ChatMessageData $data): \App\Models\ChatMessage
                {
                    return \App\Models\ChatMessage::create([
                        'session_id' => $data->session_id,
                        'sender_type' => $data->sender_type,
                        'sender_id' => $data->sender_id,
                        'message' => $data->message,
                        'is_bot' => $data->is_bot,
                    ]);
                }
                public function transferToHuman(int $sessionId): array
                {
                    return ['success' => true, 'staff' => null, 'message' => 'Transferred'];
                }
                public function staffSendMessage(\App\Data\Chat\ChatMessageData $data, int $staffUserId): \App\Models\ChatMessage
                {
                    return \App\Models\ChatMessage::create([
                        'session_id' => $data->session_id,
                        'sender_type' => 'staff',
                        'sender_id' => $staffUserId,
                        'message' => $data->message,
                        'is_bot' => false,
                    ]);
                }
                public function getNewMessages(int $sessionId, int $lastMessageId = 0): \Illuminate\Database\Eloquent\Collection
                {
                    return \App\Models\ChatMessage::where('session_id', $sessionId)
                        ->where('id', '>', $lastMessageId)
                        ->orderBy('id')
                        ->get();
                }
                public function getStaffSessions(int $staffUserId): \Illuminate\Database\Eloquent\Collection
                {
                    return \App\Models\ChatSession::whereNotNull('assigned_to')->get();
                }
            };
        });
    }

    public function test_guest_history_returns_empty_for_unknown_session(): void
    {
        $this->getJson('/api/v1/chat/guest/unknown-session/history', ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_guest_can_create_session_and_fetch_history(): void
    {
        $sessionId = 'sess_' . uniqid();

        // Create session
        $create = $this->postJson('/api/v1/chat/guest/session', [
            'session_id' => $sessionId,
            'guest_name' => 'Guest A',
            'guest_email' => 'guest@example.com',
        ], ['Accept' => 'application/json']);

        $create->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['session' => ['id'], 'messages']]);

        // History should return session object now
        $history = $this->getJson('/api/v1/chat/guest/' . $sessionId . '/history', ['Accept' => 'application/json']);
        $history->assertOk()->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['session' => ['id'], 'messages']]);
    }
}


