<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => [
                    'vi' => 'Chăm sóc da mặt',
                    'en' => 'Facial Care',
                    'ja' => 'フェイシャルケア',
                    'zh' => '面部护理',
                ],
                'slug' => 'cham-soc-da-mat',
                'description' => [
                    'vi' => 'Các dịch vụ chăm sóc da mặt chuyên nghiệp',
                    'en' => 'Professional facial care services',
                ],
                'icon' => 'fa-face',
                'display_order' => 1,
            ],
            [
                'name' => [
                    'vi' => 'Điều trị mụn',
                    'en' => 'Acne Treatment',
                    'ja' => 'ニキビ治療',
                    'zh' => '痤疮治疗',
                ],
                'slug' => 'dieu-tri-mun',
                'description' => [
                    'vi' => 'Các phương pháp điều trị mụn hiệu quả',
                    'en' => 'Effective acne treatment methods',
                ],
                'icon' => 'fa-spa',
                'display_order' => 2,
            ],
            [
                'name' => [
                    'vi' => 'Làm trắng da',
                    'en' => 'Skin Whitening',
                    'ja' => '美白',
                    'zh' => '美白',
                ],
                'slug' => 'lam-trang-da',
                'description' => [
                    'vi' => 'Dịch vụ làm trắng da an toàn và hiệu quả',
                    'en' => 'Safe and effective skin whitening services',
                ],
                'icon' => 'fa-sun',
                'display_order' => 3,
            ],
            [
                'name' => [
                    'vi' => 'Chống lão hóa',
                    'en' => 'Anti-aging',
                    'ja' => 'アンチエイジング',
                    'zh' => '抗衰老',
                ],
                'slug' => 'chong-lao-hoa',
                'description' => [
                    'vi' => 'Các liệu pháp chống lão hóa tiên tiến',
                    'en' => 'Advanced anti-aging treatments',
                ],
                'icon' => 'fa-leaf',
                'display_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::create($category);
        }
    }
}