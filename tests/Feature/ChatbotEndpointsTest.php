<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        // Provide fake Gemini key
        config(['services.gemini.key' => 'test-key']);
    }

    public function test_guest_can_chat_and_get_history_by_session_key(): void
    {
        $apiUrl = config('chatbot.gemini.api_url');
        Http::fake([
            $apiUrl => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [ ['text' => 'Xin chào, tôi có thể giúp gì?'] ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $res = $this->postJson('/api/v1/chatbot', [ 'message' => 'Hello' ]);
        $res->assertOk()
            ->assertJsonPath('data.message', 'Xin chào, tôi có thể giúp gì?')
            ->assertJsonStructure(['success','message','data' => ['message','user_id','locale','session_key'],'trace_id','timestamp']);

        $sessionKey = $res->json('data.session_key');

        $history = $this->getJson('/api/v1/chatbot/history?session_key='.$sessionKey);
        $history->assertOk()
            ->assertJsonStructure(['success','message','data' => ['messages'],'trace_id','timestamp']);
    }

    public function test_authenticated_user_can_chat(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $apiUrl = config('chatbot.gemini.api_url');
        Http::fake([
            $apiUrl => Http::response([
                'candidates' => [
                    [ 'content' => [ 'parts' => [ ['text' => 'Chào bạn!'] ] ] ]
                ]
            ], 200)
        ]);

        $res = $this->postJson('/api/v1/chatbot', [ 'message' => 'Hi' ]);
        $res->assertOk()->assertJsonPath('data.user_id', $user->id);
    }
}


