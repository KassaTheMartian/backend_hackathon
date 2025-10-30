<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    public function definition(): array
    {
        $nameEn = fake('en_US')->unique()->words(2, true);
        $nameVi = fake('vi_VN')->unique()->words(2, true);
        return [
            'name' => [
                'vi' => ucfirst($nameVi),
                'en' => ucfirst($nameEn),
                'ja' => 'カテゴリ',
                'zh' => '类别',
            ],
            'slug' => str()->slug($nameEn) . '-' . fake()->unique()->randomNumber(3),
            'description' => [
                'vi' => fake('vi_VN')->sentence(6),
                'en' => fake('en_US')->sentence(6),
            ],
            'icon' => 'fa-spa',
            'display_order' => fake()->numberBetween(1, 50),
        ];
    }
}


