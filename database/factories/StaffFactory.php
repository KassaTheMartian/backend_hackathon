<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    protected $model = Staff::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'position' => $this->faker->randomElement(['Senior Therapist', 'Junior Therapist', 'Manager', 'Receptionist']),
            'specialization' => $this->faker->randomElements([
                'Facial Treatment',
                'Acne Treatment',
                'Skin Whitening',
                'Anti-Aging',
                'Body Massage',
            ], $this->faker->numberBetween(1, 3)),
            'bio' => [
                'vi' => $this->faker->paragraph(),
                'en' => $this->faker->paragraph(),
            ],
            'avatar' => $this->faker->imageUrl(400, 400),
            'years_of_experience' => $this->faker->numberBetween(1, 20),
            'certifications' => [
                $this->faker->word() . ' Certification',
                $this->faker->word() . ' License',
            ],
            'rating' => $this->faker->randomFloat(2, 3.5, 5.0),
            'total_reviews' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the staff is senior level.
     */
    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'Senior Therapist',
            'years_of_experience' => $this->faker->numberBetween(5, 20),
            'rating' => $this->faker->randomFloat(2, 4.5, 5.0),
        ]);
    }

    /**
     * Indicate that the staff is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}