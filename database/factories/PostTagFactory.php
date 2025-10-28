<?php

namespace Database\Factories;

use App\Models\PostTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostTagFactory extends Factory
{
    protected $model = PostTag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tags = [
            ['vi' => 'Chăm sóc da mặt', 'en' => 'Facial Care'],
            ['vi' => 'Trị mụn', 'en' => 'Acne Treatment'],
            ['vi' => 'Chống lão hóa', 'en' => 'Anti-Aging'],
            ['vi' => 'Dưỡng ẩm', 'en' => 'Moisturizing'],
            ['vi' => 'Tẩy tế bào chết', 'en' => 'Exfoliation'],
            ['vi' => 'Làm trắng da', 'en' => 'Whitening'],
            ['vi' => 'Massage', 'en' => 'Massage'],
            ['vi' => 'Chăm sóc tóc', 'en' => 'Hair Care'],
            ['vi' => 'Trang điểm cô dâu', 'en' => 'Bridal Makeup'],
            ['vi' => 'Nails', 'en' => 'Nails'],
            ['vi' => 'Mi giả', 'en' => 'Eyelash Extension'],
            ['vi' => 'Waxing', 'en' => 'Waxing'],
        ];

        $tag = $this->faker->randomElement($tags);
        $slug = Str::slug($tag['en']);

        return [
            'name' => $tag,
            'slug' => $slug,
        ];
    }
}
