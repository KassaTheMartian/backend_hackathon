<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Review;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rating = $this->faker->numberBetween(1, 5);
        
        // Positive reviews have more chance for high ratings
        $isPositive = $rating >= 4;
        
        $titles = [
            5 => [
                'Dịch vụ tuyệt vời!',
                'Rất hài lòng',
                'Xuất sắc, sẽ quay lại',
                'Chuyên nghiệp và tận tâm',
                'Perfect service!',
            ],
            4 => [
                'Dịch vụ tốt',
                'Hài lòng',
                'Khá ổn',
                'Đáng để thử',
                'Good experience',
            ],
            3 => [
                'Tạm được',
                'Bình thường',
                'Cần cải thiện',
                'Ổn nhưng chưa xuất sắc',
                'Average',
            ],
            2 => [
                'Không như mong đợi',
                'Chưa tốt lắm',
                'Cần cải thiện nhiều',
                'Hơi thất vọng',
                'Below expectations',
            ],
            1 => [
                'Rất thất vọng',
                'Dịch vụ kém',
                'Không hài lòng',
                'Tệ',
                'Very disappointed',
            ],
        ];
        
        $comments = [
            5 => [
                'Dịch vụ rất chuyên nghiệp, nhân viên thân thiện và tận tình. Không gian sạch sẽ, hiện đại. Kết quả làm đẹp rất ưng ý. Chắc chắn sẽ quay lại!',
                'Tôi rất hài lòng với dịch vụ tại đây. Nhân viên tư vấn rất chi tiết, kỹ thuật viên có tay nghề cao. Giá cả hợp lý so với chất lượng. Highly recommended!',
                'Lần đầu tiên đến và đã có trải nghiệm tuyệt vời. Từ lúc đặt lịch đến khi hoàn thành đều rất chu đáo. Sẽ giới thiệu cho bạn bè!',
                'Dịch vụ xuất sắc! Không gian thư giãn, nhân viên chuyên nghiệp. Kết quả vượt mong đợi. 5 sao xứng đáng!',
            ],
            4 => [
                'Nhìn chung dịch vụ tốt, nhân viên thân thiện. Chất lượng ổn, giá cả hợp lý. Sẽ quay lại nếu có dịp.',
                'Trải nghiệm khá tốt. Kỹ thuật viên có kinh nghiệm, tuy nhiên thời gian chờ hơi lâu. Nhưng kết quả đẹp nên vẫn hài lòng.',
                'Dịch vụ tốt, không gian đẹp và sạch sẽ. Nhân viên nhiệt tình. Giá hơi cao một chút nhưng chất lượng tương xứng.',
                'Hài lòng với dịch vụ. Nhân viên chu đáo, kết quả đạt yêu cầu. Sẽ tiếp tục sử dụng.',
            ],
            3 => [
                'Dịch vụ bình thường, không có gì quá nổi bật. Nhân viên thân thiện nhưng kỹ năng chưa cao. Giá cả ổn.',
                'Trải nghiệm tạm được. Có một số điểm cần cải thiện về thái độ phục vụ và thời gian chờ đợi.',
                'Chất lượng dịch vụ tương xứng với giá tiền. Không xuất sắc nhưng cũng không tệ.',
                'Ổn, nhưng so với mặt bằng chung thì chưa có gì đặc biệt. Có thể cân nhắc nơi khác lần sau.',
            ],
            2 => [
                'Hơi thất vọng với dịch vụ. Nhân viên thiếu kinh nghiệm, thời gian chờ lâu. Kết quả không như mong đợi.',
                'Chất lượng dịch vụ chưa tốt. Giá cao nhưng không tương xứng. Cần cải thiện nhiều.',
                'Không hài lòng lắm. Nhân viên chưa tư vấn kỹ, không gian chật chội. Sẽ cân nhắc kỹ trước khi quay lại.',
                'Dịch vụ không đáp ứng được mong đợi. Có nhiều điểm cần cải thiện về chất lượng và thái độ.',
            ],
            1 => [
                'Rất thất vọng với trải nghiệm lần này. Nhân viên không chuyên nghiệp, dịch vụ kém. Không khuyến khích ai đến đây.',
                'Dịch vụ tệ, nhân viên thiếu kinh nghiệm và không thân thiện. Giá cao mà chất lượng không tương xứng.',
                'Trải nghiệm rất tệ. Từ khâu tư vấn đến thực hiện đều không đạt. Lãng phí tiền và thời gian.',
                'Không hài lòng chút nào. Sẽ không bao giờ quay lại và không giới thiệu cho ai.',
            ],
        ];
        
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            // Default to null to avoid violating unique (user_id, booking_id) during bulk seeding
            'booking_id' => null,
            'service_id' => Service::inRandomOrder()->first()?->id ?? Service::factory(),
            'staff_id' => Staff::inRandomOrder()->first()?->id ?? Staff::factory(),
            'branch_id' => Branch::inRandomOrder()->first()?->id ?? Branch::factory(),
            'rating' => $rating,
            'title' => $this->faker->randomElement($titles[$rating]),
            'comment' => $this->faker->randomElement($comments[$rating]),
            'service_quality_rating' => $this->faker->optional(0.7)->numberBetween(max(1, $rating - 1), min(5, $rating + 1)),
            'staff_rating' => $this->faker->optional(0.7)->numberBetween(max(1, $rating - 1), min(5, $rating + 1)),
            'cleanliness_rating' => $this->faker->optional(0.7)->numberBetween(max(1, $rating - 1), min(5, $rating + 1)),
            'value_rating' => $this->faker->optional(0.7)->numberBetween(max(1, $rating - 1), min(5, $rating + 1)),
            'images' => $this->faker->optional(0.3)->passthrough([
                'reviews/review-' . $this->faker->uuid() . '.jpg',
                'reviews/review-' . $this->faker->uuid() . '.jpg',
            ]),
            'is_approved' => $this->faker->boolean(80), // 80% được approve
            'is_featured' => $isPositive && $this->faker->boolean(20), // 20% reviews tốt là featured
            'admin_response' => $this->faker->optional(0.3)->randomElement([
                'Cảm ơn bạn đã chia sẻ trải nghiệm. Chúng tôi rất vui khi bạn hài lòng với dịch vụ!',
                'Xin cảm ơn phản hồi của bạn. Chúng tôi sẽ tiếp tục cải thiện để phục vụ bạn tốt hơn.',
                'Chúng tôi xin lỗi vì trải nghiệm chưa được như mong đợi. Team sẽ rút kinh nghiệm và cải thiện.',
                'Cảm ơn bạn đã tin tưởng sử dụng dịch vụ. Rất mong được phục vụ bạn trong những lần tới!',
            ]),
            'responded_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
            'responded_by' => $this->faker->optional(0.3)->passthrough(
                User::where('is_admin', true)->inRandomOrder()->first()?->id
            ),
            'helpful_count' => $this->faker->numberBetween(0, 50),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indicate that the review is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    /**
     * Indicate that the review is not approved.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    /**
     * Indicate that the review is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_approved' => true,
            'rating' => $this->faker->numberBetween(4, 5),
        ]);
    }

    /**
     * Indicate that the review has admin response.
     */
    public function withResponse(): static
    {
        return $this->state(fn (array $attributes) => [
            'admin_response' => 'Cảm ơn bạn đã chia sẻ trải nghiệm. Chúng tôi rất vui khi bạn hài lòng với dịch vụ!',
            'responded_at' => now(),
            'responded_by' => User::where('is_admin', true)->inRandomOrder()->first()?->id,
        ]);
    }

    /**
     * Indicate that the review has high rating (4-5 stars).
     */
    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->numberBetween(4, 5),
        ]);
    }

    /**
     * Indicate that the review has low rating (1-2 stars).
     */
    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->numberBetween(1, 2),
        ]);
    }
}
