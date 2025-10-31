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
            $status = fake()->randomElement(['completed', 'pending', 'failed']);
            $transactionId = 'BK' . $booking->id . '_' . now()->format('YmdHis') . '_' . fake()->randomNumber(3);
            Payment::create([
                'booking_id' => $booking->id,
                'payment_code' => Payment::generatePaymentCode(),
                'amount' => $booking->total_amount ?? 0,
                'currency' => 'VND',
                'payment_method' => fake()->randomElement(['cash','vnpay']),
                'status' => $status,
                'paid_at' => $status === 'completed' ? now()->subDays(rand(0,10)) : null,
                'transaction_id' => $transactionId,
            ]);
        }

        $this->command->info('Payments seeding completed.');
    }
}


