<?php

namespace Tests\Unit\Services;

use App\Models\ChatSession;
use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Mockery as m;
use Tests\TestCase;

class ChatbotServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        Config::set('services.gemini.key', 'test-key');
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function test_get_context_returns_string(): void
    {
        $branchRepo = m::mock(BranchRepositoryInterface::class);
        $serviceRepo = m::mock(ServiceRepositoryInterface::class);
        $branchRepo->shouldReceive('getActive')->andReturn(new \Illuminate\Database\Eloquent\Collection());
        $serviceRepo->shouldReceive('all')->andReturn(new \Illuminate\Database\Eloquent\Collection());

        $svc = new ChatbotService($branchRepo, $serviceRepo);
        $context = $svc->getContext('vi');
        $this->assertIsString($context);
        $this->assertStringContainsString('===', $context);
    }

    public function test_chat_returns_ai_message_and_persists_session(): void
    {
        $branchRepo = m::mock(BranchRepositoryInterface::class);
        $serviceRepo = m::mock(ServiceRepositoryInterface::class);
        $branchRepo->shouldReceive('getActive')->andReturn(new \Illuminate\Database\Eloquent\Collection());
        $serviceRepo->shouldReceive('all')->andReturn(new \Illuminate\Database\Eloquent\Collection());

        Http::fake([
            config('chatbot.gemini.api_url') => Http::response([
                'candidates' => [[ 'content' => [ 'parts' => [[ 'text' => 'AI reply' ]] ] ]]
            ], 200)
        ]);

        $svc = new ChatbotService($branchRepo, $serviceRepo);
        $res = $svc->chat('Hello', 'vi', null, null);
        $this->assertEquals('AI reply', $res['message']);
        $this->assertNotEmpty($res['session_key']);
        $this->assertEquals(1, ChatSession::count());
    }
}


