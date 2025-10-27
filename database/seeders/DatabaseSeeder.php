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
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
        ]);

        // Create demo data
        Demo::factory()->count(20)->create();
    }
}
