<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceCategory;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facialCareCategory = ServiceCategory::where('slug', 'cham-soc-da-mat')->first();
        $acneCategory = ServiceCategory::where('slug', 'dieu-tri-mun')->first();
        $whiteningCategory = ServiceCategory::where('slug', 'lam-trang-da')->first();
        $antiAgingCategory = ServiceCategory::where('slug', 'chong-lao-hoa')->first();

        $services = [
            [
                'category_id' => $facialCareCategory->id,
                'name' => [
                    'vi' => 'Điều trị mụn chuyên sâu',
                    'en' => 'Deep Acne Treatment',
                    'ja' => '深層ニキビ治療',
                    'zh' => '深层痤疮治疗',
                ],
                'slug' => 'dieu-tri-mun-chuyen-sau',
                'description' => [
                    'vi' => 'Liệu trình điều trị mụn chuyên sâu với công nghệ hiện đại, giúp loại bỏ mụn và ngăn ngừa tái phát.',
                    'en' => 'Deep acne treatment with modern technology to eliminate acne and prevent recurrence.',
                ],
                'short_description' => [
                    'vi' => 'Giảm mụn hiệu quả với công nghệ tiên tiến',
                    'en' => 'Effective acne reduction with advanced technology',
                ],
                'price' => 500000,
                'discounted_price' => 450000,
                'duration' => 60,
                'image' => '/storage/services/s1.jpg',
                'is_featured' => true,
                'display_order' => 1,
            ],
            [
                'category_id' => $facialCareCategory->id,
                'name' => [
                    'vi' => 'Chăm sóc da cơ bản',
                    'en' => 'Basic Facial Care',
                    'ja' => '基本フェイシャルケア',
                    'zh' => '基础面部护理',
                ],
                'slug' => 'cham-soc-da-co-ban',
                'description' => [
                    'vi' => 'Dịch vụ chăm sóc da cơ bản phù hợp cho mọi loại da.',
                    'en' => 'Basic facial care service suitable for all skin types.',
                ],
                'short_description' => [
                    'vi' => 'Chăm sóc da cơ bản cho mọi loại da',
                    'en' => 'Basic care for all skin types',
                ],
                'price' => 300000,
                'duration' => 45,
                'image' => '/storage/services/s2.jpg',
                'display_order' => 2,
            ],
            [
                'category_id' => $whiteningCategory->id,
                'name' => [
                    'vi' => 'Làm trắng da bằng laser',
                    'en' => 'Laser Skin Whitening',
                    'ja' => 'レーザー美白',
                    'zh' => '激光美白',
                ],
                'slug' => 'lam-trang-da-bang-laser',
                'description' => [
                    'vi' => 'Công nghệ laser tiên tiến giúp làm trắng da an toàn và hiệu quả.',
                    'en' => 'Advanced laser technology for safe and effective skin whitening.',
                ],
                'short_description' => [
                    'vi' => 'Làm trắng da bằng công nghệ laser',
                    'en' => 'Skin whitening with laser technology',
                ],
                'price' => 800000,
                'discounted_price' => 720000,
                'duration' => 90,
                'image' => '/storage/services/s3.jpg',
                'is_featured' => true,
                'display_order' => 1,
            ],
            [
                'category_id' => $antiAgingCategory->id,
                'name' => [
                    'vi' => 'Điều trị chống lão hóa',
                    'en' => 'Anti-aging Treatment',
                    'ja' => 'アンチエイジング治療',
                    'zh' => '抗衰老治疗',
                ],
                'slug' => 'dieu-tri-chong-lao-hoa',
                'description' => [
                    'vi' => 'Liệu pháp chống lão hóa toàn diện giúp trẻ hóa làn da.',
                    'en' => 'Comprehensive anti-aging treatment to rejuvenate the skin.',
                ],
                'short_description' => [
                    'vi' => 'Trẻ hóa làn da với liệu pháp chống lão hóa',
                    'en' => 'Rejuvenate skin with anti-aging treatment',
                ],
                'price' => 1200000,
                'duration' => 120,
                'image' => '/storage/services/s4.jpg',
                'is_featured' => true,
                'display_order' => 1,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}