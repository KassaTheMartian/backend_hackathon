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
        // Tạo Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '0123456789',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'language_preference' => 'vi',
            'avatar' => 'https://ui-avatars.com/api/?name=Admin+User&background=random',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => '123 Admin Street, Hanoi',
            'is_active' => true,
            'is_admin' => true,
            'last_login_at' => now(),
        ]);

        // Tạo Test User
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0987654321',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'language_preference' => 'vi',
            'avatar' => 'https://ui-avatars.com/api/?name=Test+User&background=random',
            'date_of_birth' => '1995-05-15',
            'gender' => 'female',
            'address' => '456 Test Street, Ho Chi Minh City',
            'is_active' => true,
            'is_admin' => false,
            'last_login_at' => now(),
        ]);

        // Tạo thêm 10 users thường
        User::factory()->count(10)->create([
            'is_admin' => false,
        ]);

        // Tạo thêm 2 admin users
        User::create([
            'name' => 'Manager 1',
            'email' => 'manager1@example.com',
            'phone' => '0111222333',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'language_preference' => 'vi',
            'is_active' => true,
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'Manager 2',
            'email' => 'manager2@example.com',
            'phone' => '0444555666',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'language_preference' => 'en',
            'is_active' => true,
            'is_admin' => true,
        ]);
    }
}

