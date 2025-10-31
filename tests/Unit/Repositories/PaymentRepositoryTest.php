<?php

namespace Tests\Unit\Repositories;

use App\Models\Payment;
use App\Repositories\Eloquent\PaymentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_get_with_filters_and_find_by_transaction(): void
    {
        $repo = new PaymentRepository(new Payment());
        $paginator = $repo->getWithFilters(['per_page' => 5]);
        $this->assertEquals(5, $paginator->perPage());
        $this->assertNull($repo->findByTransactionId('not-exist'));
    }
}


