<?php

namespace Database\Factories;

use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostCategoryFactory extends Factory
{
    protected $model = PostCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            ['vi' => 'Làm đẹp', 'en' => 'Beauty'],
            ['vi' => 'Chăm sóc da', 'en' => 'Skincare'],
            ['vi' => 'Trang điểm', 'en' => 'Makeup'],
            ['vi' => 'Chăm sóc tóc', 'en' => 'Hair Care'],
            ['vi' => 'Spa & Massage', 'en' => 'Spa & Massage'],
            ['vi' => 'Mẹo hay', 'en' => 'Tips & Tricks'],
            ['vi' => 'Xu hướng', 'en' => 'Trends'],
            ['vi' => 'Khuyến mãi', 'en' => 'Promotions'],
        ];

        $category = $this->faker->randomElement($categories);
        $slug = Str::slug($category['en']);

        return [
            'name' => $category,
            'slug' => $slug,
            'description' => [
                'vi' => $this->faker->sentence(10),
                'en' => $this->faker->sentence(10),
            ],
            'parent_id' => null,
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
