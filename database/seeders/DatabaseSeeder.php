<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Demo;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in order
        $this->call([
            UserSeeder::class,           // Users and Admins
            ServiceCategorySeeder::class, // Service Categories
            ServiceSeeder::class,        // Services
            BranchSeeder::class,         // Branches
            StaffSeeder::class,          // Staff Members
            BookingSeeder::class,        // Bookings
            ReviewSeeder::class,         // Reviews (phải sau BookingSeeder)
            PostSeeder::class,           // Posts (Blog/News)
        ]);

        // Create demo data
        Demo::factory()->count(20)->create();
    }
}
