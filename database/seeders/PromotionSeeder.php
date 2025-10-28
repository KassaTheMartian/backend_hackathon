<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\Booking;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding promotions...');

        // Basic promotions
        $promotions = [
            [
                'code' => 'WELCOME10',
                'name' => ['vi' => 'Giảm 10% cho khách mới', 'en' => '10% off for new customers'],
                'description' => ['vi' => 'Áp dụng cho đơn đầu tiên'],
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'min_amount' => 0,
                'max_discount' => 100000,
                'max_uses' => 1000,
                'max_uses_per_user' => 1,
                'applicable_to' => 'all',
                'valid_from' => now()->subDays(7),
                'valid_to' => now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'code' => 'COMBO50K',
                'name' => ['vi' => 'Giảm 50,000 cho combo dịch vụ', 'en' => '50,000 off combo'],
                'description' => ['vi' => 'Áp dụng cho một số dịch vụ'],
                'discount_type' => 'fixed_amount',
                'discount_value' => 50000,
                'min_amount' => 300000,
                'max_discount' => null,
                'max_uses' => 500,
                'max_uses_per_user' => 2,
                'applicable_to' => 'specific',
                'applicable_services' => [1,2,3],
                'valid_from' => now()->subDays(3),
                'valid_to' => now()->addMonth(),
                'is_active' => true,
            ],
        ];

        foreach ($promotions as $data) {
            Promotion::updateOrCreate(['code' => $data['code']], $data);
        }

        // Create some usages
        $booking = Booking::query()->inRandomOrder()->first();
        if ($booking) {
            $promo = Promotion::where('code', 'WELCOME10')->first();
            if ($promo) {
                PromotionUsage::create([
                    'promotion_id' => $promo->id,
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->id,
                    'discount_amount' => 50000,
                ]);
            }
        }

        $this->command->info('Promotions seeding completed.');
    }
}


