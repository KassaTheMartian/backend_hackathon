<?php

namespace Database\Seeders;

use App\Models\User;
// use App\Models\Demo;
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
            BranchServiceSeeder::class,  // Branch-Service assignments
            StaffSeeder::class,          // Staff Members
            StaffServiceSeeder::class,   // Staff-Service assignments
            BookingSeeder::class,        // Bookings
            ReviewSeeder::class,         // Reviews (phải sau BookingSeeder)
            PostSeeder::class,           // Posts (Blog/News)
            PaymentSeeder::class,        // Payments (derived from bookings)
            PromotionSeeder::class,      // Promotions & usages
            ContactSeeder::class,        // Contact submissions
        ]);

        // Demo data removed
    }
}
