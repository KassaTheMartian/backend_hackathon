<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Service;
use Illuminate\Database\Seeder;

class BranchServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceIds = Service::active()->pluck('id');
        if ($serviceIds->isEmpty()) {
            $this->command?->warn('No services found. Run ServiceSeeder first.');
            return;
        }

        Branch::active()->chunk(100, function ($branches) use ($serviceIds) {
            foreach ($branches as $branch) {
                // Pick 5-12 random services for each branch
                $count = min(max(5, (int) floor($serviceIds->count() / 3)), 12);
                $selected = $serviceIds->random(min($count, $serviceIds->count()))->values();

                $attachData = [];
                foreach ($selected as $sid) {
                    $attachData[$sid] = [
                        'is_available' => (bool) random_int(0, 1),
                        'custom_price' => random_int(0, 1) ? fake()->randomFloat(2, 10, 200) : null,
                    ];
                }

                $branch->services()->syncWithoutDetaching($attachData);
            }
        });
    }
}


