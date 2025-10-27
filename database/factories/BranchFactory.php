<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => [
                'vi' => $this->faker->company() . ' - Chi nhánh ' . $this->faker->city(),
                'en' => $this->faker->company() . ' - ' . $this->faker->city() . ' Branch',
            ],
            'slug' => $this->faker->unique()->slug(),
            'address' => [
                'vi' => $this->faker->streetAddress() . ', ' . $this->faker->city(),
                'en' => $this->faker->streetAddress() . ', ' . $this->faker->city(),
            ],
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'latitude' => $this->faker->latitude(10.0, 11.0),
            'longitude' => $this->faker->longitude(106.0, 107.0),
            'opening_hours' => [
                'monday' => ['08:00', '18:00'],
                'tuesday' => ['08:00', '18:00'],
                'wednesday' => ['08:00', '18:00'],
                'thursday' => ['08:00', '18:00'],
                'friday' => ['08:00', '18:00'],
                'saturday' => ['08:00', '18:00'],
                'sunday' => ['09:00', '17:00'],
            ],
            'images' => [
                $this->faker->imageUrl(800, 600),
                $this->faker->imageUrl(800, 600),
            ],
            'description' => [
                'vi' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'amenities' => $this->faker->randomElements(['WiFi', 'Parking', 'Air Conditioning', 'Cash Payment', 'Credit Card'], $this->faker->numberBetween(2, 5)),
            'is_active' => $this->faker->boolean(90),
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }

    /**
     * Indicate that the branch is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}