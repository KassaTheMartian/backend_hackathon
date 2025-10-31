<?php

namespace Tests\Unit\Repositories;

use App\Models\Promotion;
use App\Models\User;
use App\Repositories\Eloquent\PromotionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_get_active_and_get_by_code(): void
    {
        $repo = new PromotionRepository(new Promotion());
        $this->assertIsIterable($repo->getActive());
        $this->assertNull($repo->getByCode('NOT-EXIST'));
    }

    public function test_get_user_promotions(): void
    {
        $repo = new PromotionRepository(new Promotion());
        $user = User::factory()->create();
        $this->assertIsIterable($repo->getUserPromotions($user));
    }
}


