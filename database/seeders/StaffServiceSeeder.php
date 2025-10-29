<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = Service::pluck('id');
        if ($services->isEmpty()) {
            $this->command?->warn('No services found. Run ServiceSeeder first.');
            return;
        }

        Staff::with('services')->chunk(100, function ($staffChunk) use ($services) {
            foreach ($staffChunk as $staff) {
                $count = random_int(2, min(4, $services->count()));
                $attachIds = $services->random($count)->values()->all();
                $staff->services()->syncWithoutDetaching($attachIds);
            }
        });
    }
}


