<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bookingDate = $this->faker->dateTimeBetween('now', '+30 days');
        $servicePrice = $this->faker->numberBetween(200000, 2000000);
        $discountAmount = $this->faker->optional(0.3)->numberBetween(0, $servicePrice * 0.2) ?? 0;
        
        return [
            'booking_code' => Booking::generateBookingCode(),
            'user_id' => User::factory(),
            'guest_name' => $this->faker->name(),
            'guest_email' => $this->faker->safeEmail(),
            'guest_phone' => $this->faker->phoneNumber(),
            'branch_id' => Branch::factory(),
            'service_id' => Service::factory(),
            'staff_id' => Staff::factory(),
            'booking_date' => $bookingDate,
            'booking_time' => $this->faker->time('H:i'),
            'duration' => $this->faker->numberBetween(30, 180),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'completed', 'cancelled']),
            'cancellation_reason' => $this->faker->optional(0.1)->sentence(),
            'cancelled_by' => $this->faker->optional(0.1)->randomElement([1, 2, 3]), // User IDs
            'cancelled_at' => $this->faker->optional(0.1)->dateTimeBetween('-30 days', 'now'),
            'service_price' => $servicePrice,
            'discount_amount' => $discountAmount,
            'total_amount' => $servicePrice - $discountAmount,
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'online', 'stripe']),
            'notes' => $this->faker->optional(0.3)->paragraph(),
            'admin_notes' => $this->faker->optional(0.2)->paragraph(),
            'reminder_sent' => $this->faker->boolean(70),
            'confirmation_sent' => $this->faker->boolean(80),
        ];
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Indicate that the booking is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'confirmation_sent' => true,
        ]);
    }

    /**
     * Indicate that the booking is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'confirmation_sent' => true,
            'reminder_sent' => true,
        ]);
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancellation_reason' => $this->faker->sentence(),
            'cancelled_by' => $this->faker->randomElement([1, 2, 3]), // User IDs
            'cancelled_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }
}