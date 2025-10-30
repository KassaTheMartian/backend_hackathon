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
                'name' => ['vi' => 'Làm đẹp', 'en' => 'Beauty', 'ja' => 'ビューティー', 'zh' => '美容'],
                'slug' => 'beauty',
                'description' => [
                    'vi' => 'Tin tức và bài viết về làm đẹp',
                    'en' => 'News and articles about beauty',
                    'ja' => '美容に関するニュースと記事',
                    'zh' => '关于美容的新闻和文章'
                ],
                'display_order' => 1,
            ],
            [
                'name' => ['vi' => 'Chăm sóc da', 'en' => 'Skincare', 'ja' => 'スキンケア', 'zh' => '护肤'],
                'slug' => 'skincare',
                'description' => [
                    'vi' => 'Hướng dẫn chăm sóc da hiệu quả',
                    'en' => 'Effective skincare guides',
                    'ja' => '効果的なスキンケアガイド',
                    'zh' => '有效的护肤指南'
                ],
                'display_order' => 2,
            ],
            [
                'name' => ['vi' => 'Spa & Massage', 'en' => 'Spa & Massage', 'ja' => 'スパ＆マッサージ', 'zh' => '水疗与按摩'],
                'slug' => 'spa-massage',
                'description' => [
                    'vi' => 'Thông tin về các dịch vụ spa và massage',
                    'en' => 'Information about spa and massage services',
                    'ja' => 'スパとマッサージのサービス情報',
                    'zh' => '水疗和按摩服务信息'
                ],
                'display_order' => 3,
            ],
            [
                'name' => ['vi' => 'Mẹo hay', 'en' => 'Tips & Tricks', 'ja' => 'お役立ち情報', 'zh' => '实用技巧'],
                'slug' => 'tips-tricks',
                'description' => [
                    'vi' => 'Các mẹo làm đẹp hữu ích',
                    'en' => 'Useful beauty tips',
                    'ja' => '役立つ美容のヒント',
                    'zh' => '实用的美容技巧'
                ],
                'display_order' => 4,
            ],
            [
                'name' => ['vi' => 'Xu hướng', 'en' => 'Trends', 'ja' => 'トレンド', 'zh' => '趋势'],
                'slug' => 'trends',
                'description' => [
                    'vi' => 'Xu hướng làm đẹp mới nhất',
                    'en' => 'Latest beauty trends',
                    'ja' => '最新の美容トレンド',
                    'zh' => '最新的美容趋势'
                ],
                'display_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            PostCategory::create($category);
        }

        // Create tags
        $tags = [
            ['name' => ['vi' => 'Chăm sóc da mặt', 'en' => 'Facial Care', 'ja' => 'フェイシャルケア', 'zh' => '面部护理'], 'slug' => 'facial-care'],
            ['name' => ['vi' => 'Trị mụn', 'en' => 'Acne Treatment', 'ja' => 'ニキビ治療', 'zh' => '痤疮治疗'], 'slug' => 'acne-treatment'],
            ['name' => ['vi' => 'Chống lão hóa', 'en' => 'Anti-Aging', 'ja' => 'アンチエイジング', 'zh' => '抗衰老'], 'slug' => 'anti-aging'],
            ['name' => ['vi' => 'Dưỡng ẩm', 'en' => 'Moisturizing', 'ja' => '保湿', 'zh' => '保湿'], 'slug' => 'moisturizing'],
            ['name' => ['vi' => 'Tẩy tế bào chết', 'en' => 'Exfoliation', 'ja' => '角質除去', 'zh' => '去角质'], 'slug' => 'exfoliation'],
            ['name' => ['vi' => 'Làm trắng da', 'en' => 'Whitening', 'ja' => '美白', 'zh' => '美白'], 'slug' => 'whitening'],
            ['name' => ['vi' => 'Massage', 'en' => 'Massage', 'ja' => 'マッサージ', 'zh' => '按摩'], 'slug' => 'massage'],
            ['name' => ['vi' => 'Chăm sóc tóc', 'en' => 'Hair Care', 'ja' => 'ヘアケア', 'zh' => '护发'], 'slug' => 'hair-care'],
            ['name' => ['vi' => 'Trang điểm', 'en' => 'Makeup', 'ja' => 'メイクアップ', 'zh' => '彩妆'], 'slug' => 'makeup'],
            ['name' => ['vi' => 'Nails', 'en' => 'Nails', 'ja' => 'ネイル', 'zh' => '美甲'], 'slug' => 'nails'],
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
