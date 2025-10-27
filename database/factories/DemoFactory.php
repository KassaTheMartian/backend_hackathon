<?php

namespace Database\Factories;

use App\Models\Demo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Demo>
 */
class DemoFactory extends Factory
{
    protected $model = Demo::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'is_active' => $this->faker->boolean(85),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}


