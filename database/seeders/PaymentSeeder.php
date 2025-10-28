<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Seed payments based on existing bookings.
     */
    public function run(): void
    {
        $this->command->info('Seeding payments...');

        $bookings = Booking::query()->inRandomOrder()->limit(50)->get();
        foreach ($bookings as $booking) {
            if (Payment::where('booking_id', $booking->id)->exists()) {
                continue;
            }
            $status = fake()->randomElement(['completed','pending','failed']);
            $transactionId = 'BK' . $booking->id . '_' . now()->format('YmdHis') . '_' . fake()->randomNumber(3);
            Payment::create([
                'booking_id' => $booking->id,
                'payment_code' => 'PM' . strtoupper(bin2hex(random_bytes(4))),
                'amount' => $booking->total_amount,
                'currency' => 'VND',
                'payment_method' => fake()->randomElement(['cash','card','stripe','bank_transfer']),
                'stripe_payment_intent_id' => null,
                'stripe_charge_id' => null,
                'gateway_response' => null,
                'status' => $status,
                'paid_at' => $status === 'completed' ? now()->subDays(rand(0,10)) : null,
                'transaction_id' => $transactionId,
                'metadata' => [
                    'note' => 'seeded payment'
                ],
            ]);
        }

        $this->command->info('Payments seeding completed.');
    }
}


