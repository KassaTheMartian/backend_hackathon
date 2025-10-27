<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->numberBetween(200000, 2000000);
        $hasDiscount = $this->faker->boolean(30);

        return [
            'category_id' => ServiceCategory::factory(),
            'name' => [
                'vi' => $this->faker->sentence(3),
                'en' => $this->faker->sentence(3),
            ],
            'slug' => $this->faker->unique()->slug(),
            'description' => [
                'vi' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'short_description' => [
                'vi' => $this->faker->sentence(),
                'en' => $this->faker->sentence(),
            ],
            'price' => $price,
            'discounted_price' => $hasDiscount ? $this->faker->numberBetween($price * 0.7, $price * 0.9) : null,
            'duration' => $this->faker->numberBetween(30, 180),
            'image' => $this->faker->imageUrl(800, 600),
            'gallery' => [
                $this->faker->imageUrl(800, 600),
                $this->faker->imageUrl(800, 600),
            ],
            'is_featured' => $this->faker->boolean(40),
            'is_active' => $this->faker->boolean(90),
            'display_order' => $this->faker->numberBetween(1, 20),
            'meta_title' => [
                'vi' => $this->faker->sentence(),
                'en' => $this->faker->sentence(),
            ],
            'meta_description' => [
                'vi' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'meta_keywords' => [
                'vi' => $this->faker->words(5, true),
                'en' => $this->faker->words(5, true),
            ],
            'views_count' => $this->faker->numberBetween(0, 1000),
        ];
    }

    /**
     * Indicate that the service is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the service is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}