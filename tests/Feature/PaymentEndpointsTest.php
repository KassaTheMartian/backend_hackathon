<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_guest_cannot_list_payments(): void
    {
        $res = $this->getJson('/api/v1/payments');
        $res->assertStatus(401);
    }

    public function test_user_can_list_payments(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $res = $this->getJson('/api/v1/payments');
        // Envelope with success true and meta for pagination OR array
        $res->assertOk()->assertJsonStructure([
            'success','message','data','meta','trace_id','timestamp'
        ]);
    }

    public function test_vnpay_create_requires_params(): void
    {
        $res = $this->postJson('/api/v1/payments/vnpay/create', []);
        // Expect validation error style; current app may return 400
        $res->assertStatus(400);
    }

    public function test_vnpay_refund_requires_params(): void
    {
        $res = $this->postJson('/api/v1/payments/vnpay/refund', []);
        $res->assertStatus(400);
    }

    public function test_vnpay_query_requires_params(): void
    {
        $res = $this->postJson('/api/v1/payments/vnpay/query', []);
        $res->assertStatus(400);
    }

    public function test_vnpay_return_endpoint_reachable(): void
    {
        $res = $this->getJson('/api/v1/payments/vnpay/return');
        // May be 200 or 400 depending on params; ensure not 404
        $this->assertTrue(in_array($res->getStatusCode(), [200, 400, 422]));
    }

    public function test_vnpay_ipn_endpoint_reachable(): void
    {
        $res = $this->postJson('/api/v1/payments/vnpay/ipn', []);
        $this->assertTrue(in_array($res->getStatusCode(), [200, 400, 422]));
    }
}


