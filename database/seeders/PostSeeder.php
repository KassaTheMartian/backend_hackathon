<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        $this->command->info('Clearing existing post data...');
        \DB::table('post_tag_pivot')->delete();
        Post::query()->delete();
        PostTag::query()->delete();
        PostCategory::query()->delete();

        // Create categories
        $categories = [
            [
                'name' => ['vi' => 'Làm đẹp', 'en' => 'Beauty'],
                'slug' => 'beauty',
                'description' => [
                    'vi' => 'Tin tức và bài viết về làm đẹp',
                    'en' => 'News and articles about beauty'
                ],
                'display_order' => 1,
            ],
            [
                'name' => ['vi' => 'Chăm sóc da', 'en' => 'Skincare'],
                'slug' => 'skincare',
                'description' => [
                    'vi' => 'Hướng dẫn chăm sóc da hiệu quả',
                    'en' => 'Effective skincare guides'
                ],
                'display_order' => 2,
            ],
            [
                'name' => ['vi' => 'Spa & Massage', 'en' => 'Spa & Massage'],
                'slug' => 'spa-massage',
                'description' => [
                    'vi' => 'Thông tin về các dịch vụ spa và massage',
                    'en' => 'Information about spa and massage services'
                ],
                'display_order' => 3,
            ],
            [
                'name' => ['vi' => 'Mẹo hay', 'en' => 'Tips & Tricks'],
                'slug' => 'tips-tricks',
                'description' => [
                    'vi' => 'Các mẹo làm đẹp hữu ích',
                    'en' => 'Useful beauty tips'
                ],
                'display_order' => 4,
            ],
            [
                'name' => ['vi' => 'Xu hướng', 'en' => 'Trends'],
                'slug' => 'trends',
                'description' => [
                    'vi' => 'Xu hướng làm đẹp mới nhất',
                    'en' => 'Latest beauty trends'
                ],
                'display_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            PostCategory::create($category);
        }

        // Create tags
        $tags = [
            ['name' => ['vi' => 'Chăm sóc da mặt', 'en' => 'Facial Care'], 'slug' => 'facial-care'],
            ['name' => ['vi' => 'Trị mụn', 'en' => 'Acne Treatment'], 'slug' => 'acne-treatment'],
            ['name' => ['vi' => 'Chống lão hóa', 'en' => 'Anti-Aging'], 'slug' => 'anti-aging'],
            ['name' => ['vi' => 'Dưỡng ẩm', 'en' => 'Moisturizing'], 'slug' => 'moisturizing'],
            ['name' => ['vi' => 'Tẩy tế bào chết', 'en' => 'Exfoliation'], 'slug' => 'exfoliation'],
            ['name' => ['vi' => 'Làm trắng da', 'en' => 'Whitening'], 'slug' => 'whitening'],
            ['name' => ['vi' => 'Massage', 'en' => 'Massage'], 'slug' => 'massage'],
            ['name' => ['vi' => 'Chăm sóc tóc', 'en' => 'Hair Care'], 'slug' => 'hair-care'],
            ['name' => ['vi' => 'Trang điểm', 'en' => 'Makeup'], 'slug' => 'makeup'],
            ['name' => ['vi' => 'Nails', 'en' => 'Nails'], 'slug' => 'nails'],
        ];

        foreach ($tags as $tag) {
            PostTag::create($tag);
        }

        // Create posts
        $this->command->info('Creating published posts...');
        Post::factory()
            ->count(30)
            ->published()
            ->create()
            ->each(function ($post) {
                // Attach 2-4 random tags to each post
                $tags = PostTag::inRandomOrder()->limit(rand(2, 4))->pluck('id');
                $post->tags()->attach($tags);
            });

        // Create featured posts
        $this->command->info('Creating featured posts...');
        Post::factory()
            ->count(5)
            ->featured()
            ->create()
            ->each(function ($post) {
                $tags = PostTag::inRandomOrder()->limit(rand(2, 4))->pluck('id');
                $post->tags()->attach($tags);
            });

        // Create draft posts
        $this->command->info('Creating draft posts...');
        Post::factory()
            ->count(10)
            ->draft()
            ->create()
            ->each(function ($post) {
                $tags = PostTag::inRandomOrder()->limit(rand(2, 4))->pluck('id');
                $post->tags()->attach($tags);
            });

        $this->command->info('Post seeding completed!');
        $this->command->info('Total categories: ' . PostCategory::count());
        $this->command->info('Total tags: ' . PostTag::count());
        $this->command->info('Total posts: ' . Post::count());
        $this->command->info('Published posts: ' . Post::where('status', 'published')->count());
        $this->command->info('Featured posts: ' . Post::where('is_featured', true)->count());
        $this->command->info('Draft posts: ' . Post::where('status', 'draft')->count());
    }
}
