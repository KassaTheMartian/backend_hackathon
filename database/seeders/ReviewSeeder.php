<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding reviews...');

        // Lấy tất cả bookings đã completed để tạo review
        $completedBookings = Booking::where('status', 'completed')
            ->with(['user', 'service', 'staff', 'branch'])
            ->get();

        if ($completedBookings->isEmpty()) {
            $this->command->warn('No completed bookings found. Creating sample reviews without specific bookings...');
            
            // Tạo 50 reviews mẫu
            Review::factory(50)->create();
            
            // Tạo 20 featured reviews (rating cao)
            Review::factory(20)->featured()->create();
            
            // Tạo 10 pending reviews (chưa approve)
            Review::factory(10)->pending()->create();
            
            $this->command->info('Created 80 sample reviews');
            return;
        }

        $this->command->info("Found {$completedBookings->count()} completed bookings");

        // Tạo review cho 70% completed bookings
        $bookingsToReview = $completedBookings->random(
            min(ceil($completedBookings->count() * 0.7), $completedBookings->count())
        );

        $reviewsCreated = 0;

        foreach ($bookingsToReview as $booking) {
            // Kiểm tra xem booking đã có review chưa
            if ($booking->reviews()->exists()) {
                continue;
            }

            // Random rating with bias towards positive (70% là 4-5 sao)
            $isPositive = rand(1, 100) <= 70;
            $rating = $isPositive 
                ? rand(4, 5) 
                : rand(1, 3);

            $review = Review::factory()->create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'service_id' => $booking->service_id,
                'staff_id' => $booking->staff_id,
                'branch_id' => $booking->branch_id,
                'rating' => $rating,
                'is_approved' => rand(1, 100) <= 85, // 85% được approve
            ]);

            // 30% reviews sẽ có admin response
            if (rand(1, 100) <= 30) {
                $admin = User::where('is_admin', true)->inRandomOrder()->first();
                if ($admin) {
                    $responses = [
                        'Cảm ơn bạn đã chia sẻ trải nghiệm. Chúng tôi rất vui khi bạn hài lòng với dịch vụ!',
                        'Xin cảm ơn phản hồi của bạn. Chúng tôi sẽ tiếp tục cải thiện để phục vụ bạn tốt hơn.',
                        'Chúng tôi xin lỗi vì trải nghiệm chưa được như mong đợi. Team sẽ rút kinh nghiệm và cải thiện.',
                        'Cảm ơn bạn đã tin tưởng sử dụng dịch vụ. Rất mong được phục vụ bạn trong những lần tới!',
                        'Cảm ơn đánh giá của bạn! Chúng tôi luôn nỗ lực mang đến trải nghiệm tốt nhất.',
                    ];

                    $review->update([
                        'admin_response' => $responses[array_rand($responses)],
                        'responded_at' => now()->subDays(rand(0, 7)),
                        'responded_by' => $admin->id,
                    ]);
                }
            }

            // 15% reviews tốt (4-5 sao) sẽ là featured
            if ($rating >= 4 && rand(1, 100) <= 15) {
                $review->update(['is_featured' => true]);
            }

            $reviewsCreated++;
        }

        $this->command->info("Created {$reviewsCreated} reviews for completed bookings");

        // Tạo thêm một số featured reviews để highlight
        $additionalFeatured = Review::factory(10)
            ->featured()
            ->withResponse()
            ->create();

        $this->command->info("Created 10 additional featured reviews");

        // Tạo một số pending reviews (chưa approve)
        $pendingReviews = Review::factory(5)->pending()->create();

        $this->command->info("Created 5 pending reviews");

        $totalReviews = Review::count();
        $approvedCount = Review::where('is_approved', true)->count();
        $featuredCount = Review::where('is_featured', true)->count();

        $this->command->info("✅ Review seeding completed!");
        $this->command->info("   Total reviews: {$totalReviews}");
        $this->command->info("   Approved: {$approvedCount}");
        $this->command->info("   Featured: {$featuredCount}");
        $this->command->info("   Pending: " . ($totalReviews - $approvedCount));
    }
}
