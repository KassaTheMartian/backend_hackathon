<?php

namespace Database\Seeders;

use App\Models\ContactSubmission;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding contact submissions...');

        ContactSubmission::factory()->count(20)->create();

        $this->command->info('Contact submissions seeding completed.');
    }
}


