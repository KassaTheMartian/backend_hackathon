<?php

namespace Tests\Unit\Services;

use App\Models\Promotion;
use App\Models\User;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use App\Services\PromotionService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    private $promotionRepository;
    private $promotionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->promotionRepository = Mockery::mock(PromotionRepositoryInterface::class);
        $this->promotionService = new PromotionService($this->promotionRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ========== getActivePromotions() Tests ==========

    public function test_get_active_promotions_returns_collection(): void
    {
        $promotions = new Collection([
            (object)['id' => 1, 'code' => 'PROMO10'],
            (object)['id' => 2, 'code' => 'PROMO20'],
        ]);

        $this->promotionRepository
            ->shouldReceive('getActive')
            ->once()
            ->andReturn($promotions);

        $result = $this->promotionService->getActivePromotions();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_get_active_promotions_returns_empty_when_none_active(): void
    {
        $emptyCollection = new Collection([]);

        $this->promotionRepository
            ->shouldReceive('getActive')
            ->once()
            ->andReturn($emptyCollection);

        $result = $this->promotionService->getActivePromotions();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

    // ========== getPromotionByCode() Tests ==========

    public function test_get_promotion_by_code_returns_promotion(): void
    {
        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->code = 'SAVE20';

        $this->promotionRepository
            ->shouldReceive('getByCode')
            ->once()
            ->with('SAVE20')
            ->andReturn($promotion);

        $result = $this->promotionService->getPromotionByCode('SAVE20');

        $this->assertSame($promotion, $result);
        $this->assertEquals('SAVE20', $result->code);
    }

    public function test_get_promotion_by_code_returns_null_when_not_found(): void
    {
        $this->promotionRepository
            ->shouldReceive('getByCode')
            ->once()
            ->with('INVALID')
            ->andReturn(null);

        $result = $this->promotionService->getPromotionByCode('INVALID');

        $this->assertNull($result);
    }

    // ========== validatePromotionCode() Tests ==========

    public function test_validate_promotion_code_throws_exception_when_not_found(): void
    {
        $user = Mockery::mock(User::class)->makePartial();

        $this->promotionRepository
            ->shouldReceive('getByCode')
            ->once()
            ->with('INVALID')
            ->andReturn(null);

        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage('Promotion code does not exist');

        $this->promotionService->validatePromotionCode('INVALID', $user, 100.0);
    }

    public function test_validate_promotion_code_throws_exception_when_expired(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->code = 'EXPIRED';

        $promotion->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $this->promotionRepository
            ->shouldReceive('getByCode')
            ->once()
            ->with('EXPIRED')
            ->andReturn($promotion);

        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage('Promotion code has expired or is no longer valid');

        $this->promotionService->validatePromotionCode('EXPIRED', $user, 100.0);
    }

    public function test_validate_promotion_code_throws_exception_when_cannot_be_used(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->code = 'VIP100';

        $promotion->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $promotion->shouldReceive('canBeUsedBy')
            ->once()
            ->with($user, 50.0)
            ->andReturn(false);

        $this->promotionRepository
            ->shouldReceive('getByCode')
            ->once()
            ->with('VIP100')
            ->andReturn($promotion);

        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage('This promotion code cannot be used for this order');

        $this->promotionService->validatePromotionCode('VIP100', $user, 50.0);
    }

    public function test_validate_promotion_code_returns_valid_with_discount(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->code = 'SAVE20';

        $promotion->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $promotion->shouldReceive('canBeUsedBy')
            ->once()
            ->with($user, 200.0)
            ->andReturn(true);

        $promotion->shouldReceive('calculateDiscount')
            ->once()
            ->with(200.0)
            ->andReturn(40.0);

        $this->promotionRepository
            ->shouldReceive('getByCode')
            ->once()
            ->with('SAVE20')
            ->andReturn($promotion);

        $result = $this->promotionService->validatePromotionCode('SAVE20', $user, 200.0);

        $this->assertTrue($result['valid']);
        $this->assertSame($promotion, $result['promotion']);
        $this->assertEquals(40.0, $result['discount_amount']);
        $this->assertArrayNotHasKey('message', $result);
    }

    public function test_validate_promotion_code_with_zero_amount(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->code = 'WELCOME';

        $promotion->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $promotion->shouldReceive('canBeUsedBy')
            ->once()
            ->with($user, 0.0)
            ->andReturn(true);

        $promotion->shouldReceive('calculateDiscount')
            ->once()
            ->with(0.0)
            ->andReturn(0.0);

        $this->promotionRepository
            ->shouldReceive('getByCode')
            ->once()
            ->with('WELCOME')
            ->andReturn($promotion);

        $result = $this->promotionService->validatePromotionCode('WELCOME', $user, 0.0);

        $this->assertTrue($result['valid']);
        $this->assertEquals(0.0, $result['discount_amount']);
    }

    // ========== applyPromotion() Tests ==========

    public function test_apply_promotion_records_usage_and_returns_discount(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;

        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->id = 10;

        $promotion->shouldReceive('calculateDiscount')
            ->once()
            ->with(300.0)
            ->andReturn(60.0);

        $this->promotionRepository
            ->shouldReceive('recordUsage')
            ->once()
            ->with($promotion, $user, 5, 60.0)
            ->andReturn(true);

        $result = $this->promotionService->applyPromotion($promotion, 5, $user, 300.0);

        $this->assertEquals(60.0, $result);
    }

    public function test_apply_promotion_with_zero_discount(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $promotion = Mockery::mock(Promotion::class)->makePartial();

        $promotion->shouldReceive('calculateDiscount')
            ->once()
            ->with(10.0)
            ->andReturn(0.0);

        $this->promotionRepository
            ->shouldReceive('recordUsage')
            ->once()
            ->with($promotion, $user, 1, 0.0)
            ->andReturn(true);

        $result = $this->promotionService->applyPromotion($promotion, 1, $user, 10.0);

        $this->assertEquals(0.0, $result);
    }

    public function test_apply_promotion_with_large_discount(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $promotion = Mockery::mock(Promotion::class)->makePartial();

        $promotion->shouldReceive('calculateDiscount')
            ->once()
            ->with(1000.0)
            ->andReturn(500.0);

        $this->promotionRepository
            ->shouldReceive('recordUsage')
            ->once()
            ->with($promotion, $user, 100, 500.0)
            ->andReturn(true);

        $result = $this->promotionService->applyPromotion($promotion, 100, $user, 1000.0);

        $this->assertEquals(500.0, $result);
    }

    // ========== getUserPromotions() Tests ==========

    public function test_get_user_promotions_returns_collection(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;

        $promotions = new Collection([
            (object)['id' => 1, 'code' => 'USER10'],
            (object)['id' => 2, 'code' => 'USER20'],
        ]);

        $this->promotionRepository
            ->shouldReceive('getUserPromotions')
            ->once()
            ->with($user)
            ->andReturn($promotions);

        $result = $this->promotionService->getUserPromotions($user);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_get_user_promotions_returns_empty_when_no_promotions(): void
    {
        $user = Mockery::mock(User::class)->makePartial();

        $emptyCollection = new Collection([]);

        $this->promotionRepository
            ->shouldReceive('getUserPromotions')
            ->once()
            ->with($user)
            ->andReturn($emptyCollection);

        $result = $this->promotionService->getUserPromotions($user);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

    // ========== createPromotion() Tests ==========

    public function test_create_promotion_creates_new_promotion(): void
    {
        $data = [
            'code' => 'NEW2025',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ];

        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->id = 1;
        $promotion->code = 'NEW2025';

        $this->promotionRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($promotion);

        $result = $this->promotionService->createPromotion($data);

        $this->assertSame($promotion, $result);
        $this->assertEquals('NEW2025', $result->code);
    }

    public function test_create_promotion_with_minimum_data(): void
    {
        $data = [
            'code' => 'MIN',
            'discount_value' => 10,
        ];

        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->code = 'MIN';

        $this->promotionRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($promotion);

        $result = $this->promotionService->createPromotion($data);

        $this->assertSame($promotion, $result);
    }

    // ========== updatePromotion() Tests ==========

    public function test_update_promotion_updates_existing_promotion(): void
    {
        $data = [
            'discount_value' => 25,
            'end_date' => '2025-12-31',
        ];

        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->id = 1;
        $promotion->discount_value = 25;

        $this->promotionRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($promotion);

        $result = $this->promotionService->updatePromotion(1, $data);

        $this->assertSame($promotion, $result);
        $this->assertEquals(25, $result->discount_value);
    }

    public function test_update_promotion_returns_null_when_not_found(): void
    {
        $data = ['discount_value' => 30];

        $this->promotionRepository
            ->shouldReceive('update')
            ->once()
            ->with(999, $data)
            ->andReturn(null);

        $result = $this->promotionService->updatePromotion(999, $data);

        $this->assertNull($result);
    }

    // ========== deletePromotion() Tests ==========

    public function test_delete_promotion_deletes_successfully(): void
    {
        $this->promotionRepository
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->promotionService->deletePromotion(1);

        $this->assertTrue($result);
    }

    public function test_delete_promotion_returns_false_when_not_found(): void
    {
        $this->promotionRepository
            ->shouldReceive('delete')
            ->once()
            ->with(999)
            ->andReturn(false);

        $result = $this->promotionService->deletePromotion(999);

        $this->assertFalse($result);
    }

    // ========== getPromotionStats() Tests ==========

    public function test_get_promotion_stats_returns_statistics(): void
    {
        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->id = 1;

        $stats = [
            'total_usage' => 150,
            'total_discount_given' => 3000.0,
            'unique_users' => 75,
            'average_discount' => 20.0,
        ];

        $this->promotionRepository
            ->shouldReceive('getUsageStats')
            ->once()
            ->with($promotion)
            ->andReturn($stats);

        $result = $this->promotionService->getPromotionStats($promotion);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_usage', $result);
        $this->assertArrayHasKey('total_discount_given', $result);
        $this->assertArrayHasKey('unique_users', $result);
        $this->assertArrayHasKey('average_discount', $result);
        $this->assertEquals(150, $result['total_usage']);
        $this->assertEquals(3000.0, $result['total_discount_given']);
    }

    public function test_get_promotion_stats_returns_empty_stats_for_unused_promotion(): void
    {
        $promotion = Mockery::mock(Promotion::class)->makePartial();
        $promotion->id = 1;

        $emptyStats = [
            'total_usage' => 0,
            'total_discount_given' => 0.0,
            'unique_users' => 0,
            'average_discount' => 0.0,
        ];

        $this->promotionRepository
            ->shouldReceive('getUsageStats')
            ->once()
            ->with($promotion)
            ->andReturn($emptyStats);

        $result = $this->promotionService->getPromotionStats($promotion);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['total_usage']);
        $this->assertEquals(0.0, $result['total_discount_given']);
    }

    public function test_get_promotion_stats_handles_complex_statistics(): void
    {
        $promotion = Mockery::mock(Promotion::class)->makePartial();

        $stats = [
            'total_usage' => 1000,
            'total_discount_given' => 50000.0,
            'unique_users' => 250,
            'average_discount' => 50.0,
            'most_used_by' => 'Premium Users',
            'peak_usage_date' => '2025-11-11',
        ];

        $this->promotionRepository
            ->shouldReceive('getUsageStats')
            ->once()
            ->with($promotion)
            ->andReturn($stats);

        $result = $this->promotionService->getPromotionStats($promotion);

        $this->assertCount(6, $result);
        $this->assertEquals(1000, $result['total_usage']);
        $this->assertEquals('Premium Users', $result['most_used_by']);
    }
}
