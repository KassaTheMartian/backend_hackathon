<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy dữ liệu đã có (từ các seeder trước)
        $users = User::where('is_admin', false)->get();
        
        if ($users->isEmpty()) {
            $users = User::factory()->count(10)->create(['is_admin' => false]);
        }
        
        $branches = Branch::all();
        
        if ($branches->isEmpty()) {
            $branches = Branch::factory()->count(3)->create();
        }
        
        $staff = Staff::all();
        
        if ($staff->isEmpty()) {
            $staff = Staff::factory()->count(5)->create();
        }
        
        $services = Service::all();
        
        if ($services->isEmpty()) {
            $this->command->error('No services found. Please run ServiceSeeder first.');
            return;
        }
        
        // Tạo bookings với trạng thái pending
        Booking::factory()
            ->count(10)
            ->pending()
            ->state([
                'user_id' => fn() => $users->random()->id,
                'branch_id' => fn() => $branches->random()->id,
                'staff_id' => fn() => $staff->random()->id,
                'service_id' => fn() => $services->random()->id,
            ])
            ->create();
            
        // Tạo bookings với trạng thái confirmed
        Booking::factory()
            ->count(15)
            ->confirmed()
            ->state([
                'user_id' => fn() => $users->random()->id,
                'branch_id' => fn() => $branches->random()->id,
                'staff_id' => fn() => $staff->random()->id,
                'service_id' => fn() => $services->random()->id,
            ])
            ->create();
            
        // Tạo bookings với trạng thái completed
        Booking::factory()
            ->count(20)
            ->completed()
            ->state([
                'user_id' => fn() => $users->random()->id,
                'branch_id' => fn() => $branches->random()->id,
                'staff_id' => fn() => $staff->random()->id,
                'service_id' => fn() => $services->random()->id,
            ])
            ->create();
            
        // Tạo bookings với trạng thái cancelled
        Booking::factory()
            ->count(5)
            ->cancelled()
            ->state([
                'user_id' => fn() => $users->random()->id,
                'branch_id' => fn() => $branches->random()->id,
                'staff_id' => fn() => $staff->random()->id,
                'service_id' => fn() => $services->random()->id,
            ])
            ->create();
    }
}
