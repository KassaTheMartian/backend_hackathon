<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            ['vi' => '10 Bí Quyết Chăm Sóc Da Mặt Hiệu Quả', 'en' => '10 Effective Facial Care Tips'],
            ['vi' => 'Cách Trị Mụn Tại Nhà An Toàn Và Nhanh Chóng', 'en' => 'Safe and Fast Home Acne Treatment'],
            ['vi' => 'Top 5 Liệu Trình Spa Được Yêu Thích Nhất', 'en' => 'Top 5 Most Popular Spa Treatments'],
            ['vi' => 'Bí Quyết Giữ Da Mặt Luôn Tươi Trẻ', 'en' => 'Secrets to Keep Your Skin Young'],
            ['vi' => 'Chăm Sóc Da Mùa Hè - Những Điều Cần Biết', 'en' => 'Summer Skincare - What You Need to Know'],
            ['vi' => 'Xu Hướng Trang Điểm Hot Nhất Năm 2025', 'en' => 'Hottest Makeup Trends of 2025'],
            ['vi' => 'Massage Trị Liệu - Lợi Ích Bạn Chưa Biết', 'en' => 'Therapeutic Massage - Benefits You Didn\'t Know'],
            ['vi' => 'Làm Đẹp Tự Nhiên Từ Nguyên Liệu Thiên Nhiên', 'en' => 'Natural Beauty From Natural Ingredients'],
            ['vi' => 'Quy Trình Chăm Sóc Da Ban Đêm Hoàn Hảo', 'en' => 'Perfect Night Skincare Routine'],
            ['vi' => 'Cách Chọn Kem Dưỡng Phù Hợp Với Làn Da', 'en' => 'How to Choose the Right Moisturizer'],
        ];

        $title = $this->faker->randomElement($titles);
        $slug = [
            'vi' => Str::slug($title['vi']),
            'en' => Str::slug($title['en']),
        ];

        $excerpt = [
            'vi' => $this->faker->paragraph(2),
            'en' => $this->faker->paragraph(2),
        ];

        $content = [
            'vi' => $this->generateVietnameseContent(),
            'en' => $this->generateEnglishContent(),
        ];

        $status = $this->faker->randomElement(['draft', 'published', 'archived']);
        $publishedAt = $status === 'published' 
            ? $this->faker->dateTimeBetween('-6 months', 'now')
            : null;

        return [
            'author_id' => User::where('is_admin', true)->inRandomOrder()->first()?->id ?? 1,
            'category_id' => PostCategory::inRandomOrder()->first()?->id,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'featured_image' => 'posts/' . $this->faker->numberBetween(1, 10) . '.jpg',
            'images' => $this->faker->boolean(30) ? [
                'posts/gallery1.jpg',
                'posts/gallery2.jpg',
                'posts/gallery3.jpg',
            ] : null,
            'status' => $status,
            'published_at' => $publishedAt,
            'views_count' => $this->faker->numberBetween(0, 5000),
            'reading_time' => $this->faker->numberBetween(3, 15),
            'is_featured' => $this->faker->boolean(20),
            'allow_comments' => $this->faker->boolean(80),
            'meta_title' => $title,
            'meta_description' => $excerpt,
            'meta_keywords' => [
                'vi' => 'làm đẹp, spa, chăm sóc da, skincare',
                'en' => 'beauty, spa, skincare, facial care',
            ],
        ];
    }

    /**
     * Generate Vietnamese content.
     */
    private function generateVietnameseContent(): string
    {
        $paragraphs = [
            'Làn da khỏe đẹp là mong muốn của mọi người. Để có được làn da như ý, bạn cần chú ý đến việc chăm sóc da hàng ngày.',
            'Chăm sóc da mặt không chỉ giúp bạn trông trẻ trung hơn mà còn giúp da khỏe mạnh từ bên trong. Việc làm sạch da đúng cách là bước quan trọng nhất.',
            'Dưỡng ẩm là yếu tố không thể thiếu trong quy trình chăm sóc da. Hãy chọn kem dưỡng ẩm phù hợp với loại da của bạn.',
            'Massage khuôn mặt giúp kích thích tuần hoàn máu, mang lại làn da hồng hào, tươi trẻ. Bạn nên massage da mặt ít nhất 2-3 lần mỗi tuần.',
            'Chống nắng là bước quan trọng giúp bảo vệ da khỏi tác hại của tia UV. Hãy sử dụng kem chống nắng mỗi ngày, kể cả khi trời nhiều mây.',
            'Tẩy tế bào chết giúp loại bỏ lớp da chết, mang lại làn da mịn màng, sáng khỏe. Tuy nhiên, không nên tẩy tế bào chết quá thường xuyên.',
        ];

        return '<p>' . implode('</p><p>', array_slice($paragraphs, 0, $this->faker->numberBetween(3, 6))) . '</p>';
    }

    /**
     * Generate English content.
     */
    private function generateEnglishContent(): string
    {
        $paragraphs = [];
        for ($i = 0; $i < $this->faker->numberBetween(3, 6); $i++) {
            $paragraphs[] = $this->faker->paragraph(4);
        }

        return '<p>' . implode('</p><p>', $paragraphs) . '</p>';
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the post is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the post is draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
