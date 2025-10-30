<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = fake('vi_VN');

        // Predefined important accounts (idempotent)
        $predefined = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'phone' => '0123456789',
                'language_preference' => 'vi',
                'is_admin' => true,
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '0987654321',
                'language_preference' => 'vi',
                'is_admin' => false,
            ],
            [
                'name' => 'Manager 1',
                'email' => 'manager1@example.com',
                'phone' => '0111222333',
                'language_preference' => 'vi',
                'is_admin' => true,
            ],
            [
                'name' => 'Manager 2',
                'email' => 'manager2@example.com',
                'phone' => '0444555666',
                'language_preference' => 'en',
                'is_admin' => true,
            ],
        ];

        foreach ($predefined as $acc) {
            User::updateOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'phone' => $acc['phone'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'language_preference' => $acc['language_preference'],
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($acc['name']) . '&background=random',
                    'date_of_birth' => $faker->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
                    'gender' => $faker->randomElement(['male','female']),
                    'address' => $faker->address(),
                    'is_active' => true,
                    'is_admin' => $acc['is_admin'],
                    'last_login_at' => now(),
                ]
            );
        }

        // Generate additional realistic users with diverse locales
        $locales = ['vi_VN','en_US','ja_JP'];
        foreach (range(1, 20) as $i) {
            $f = fake($locales[array_rand($locales)]);
            $name = $f->name();
            $email = $f->unique()->safeEmail();

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => $f->unique()->numerify('0#########'),
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'language_preference' => in_array($f->locale(), ['vi_VN','en_US','ja_JP']) ? substr($f->locale(), 0, 2) : 'vi',
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random',
                    'date_of_birth' => $f->dateTimeBetween('-55 years', '-18 years')->format('Y-m-d'),
                    'gender' => $f->randomElement(['male','female']),
                    'address' => $f->address(),
                    'is_active' => true,
                    'is_admin' => false,
                ]
            );
        }
    }
}

