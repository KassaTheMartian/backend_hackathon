<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class BookingEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // You might want to seed permissions/roles/users if needed.
    }

    public function test_user_can_create_booking()
    {
        $user = User::factory()->create();
        $payload = [
            'branch_id' => 1, // Should match branch id in your seed
            'service_id' => 1, // Should match service id in your seed
            'staff_id' => 1, // Should match staff id in your seed
            'booking_date' => now()->addDays(1)->toDateString(),
            'booking_time' => '10:00',
            'notes' => 'Test booking',
        ];
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/bookings', $payload);

        $response->assertCreated()->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->where('message', 'bookings.created')
                ->has('data.id')
        );
    }

    public function test_user_can_get_my_booking_detail_by_code()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->for($user)->create();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/bookings/by-code/' . $booking->booking_code);

        $response->assertOk()->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->where('data.id', $booking->id)
                ->where('data.booking_code', $booking->booking_code)
        );
    }

    public function test_user_cannot_get_another_users_booking_detail_by_code()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $booking = Booking::factory()->for($userB)->create();
        $response = $this->actingAs($userA, 'sanctum')->getJson('/api/v1/bookings/by-code/' . $booking->booking_code);
        $response->assertNotFound();
    }

    public function test_user_can_update_booking()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->for($user)->create();
        $payload = [
            'booking_date' => now()->addDays(3)->format('Y-m-d'),
            'booking_time' => '11:00',
            'notes' => 'Update test',
        ];
        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/bookings/' . $booking->id, $payload);
        $response->assertOk()->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->where('data.id', $booking->id)
        );
    }

    public function test_user_can_cancel_booking()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->for($user)->create(['status' => 'confirmed']);
        $payload = [ 'cancellation_reason' => 'No longer needed' ];
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/bookings/' . $booking->id . '/cancel', $payload);
        $response->assertOk()->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->where('data.status', 'cancelled')
        );
    }

    public function test_user_can_list_my_bookings()
    {
        $user = User::factory()->create();
        Booking::factory()->count(2)->for($user)->create();
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/my-bookings');
        $response->assertOk()->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->has('data')
        );
    }

    public function test_user_can_set_booking_paid()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->for($user)->create(['payment_status' => 'pending', 'status' => 'pending']);
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/bookings/' . $booking->id . '/set-paid');
        $response->assertOk()->assertJson(fn (AssertableJson $json) =>
            $json->where('success', true)
                ->where('data.payment_status', 'paid')
                ->where('data.status', 'completed')
        );
    }
}
