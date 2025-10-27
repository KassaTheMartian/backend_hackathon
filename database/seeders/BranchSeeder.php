<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => [
                    'vi' => 'Spa & Beauty Center - Chi nhánh Quận 1',
                    'en' => 'Spa & Beauty Center - District 1 Branch',
                ],
                'slug' => 'spa-beauty-center-quan-1',
                'address' => [
                    'vi' => '123 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh',
                    'en' => '123 Nguyen Hue Street, Ben Nghe Ward, District 1, Ho Chi Minh City',
                ],
                'phone' => '028 3829 1234',
                'email' => 'branch1@example.com',
                'latitude' => 10.7769,
                'longitude' => 106.7009,
                'opening_hours' => [
                    'monday' => ['09:00', '20:00'],
                    'tuesday' => ['09:00', '20:00'],
                    'wednesday' => ['09:00', '20:00'],
                    'thursday' => ['09:00', '20:00'],
                    'friday' => ['09:00', '20:00'],
                    'saturday' => ['08:00', '21:00'],
                    'sunday' => ['08:00', '21:00'],
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1616394584738-fc6e612e71b9?w=800&h=600',
                    'https://images.unsplash.com/photo-1600335895229-6e75511892c8?w=800&h=600',
                ],
                'description' => [
                    'vi' => 'Chi nhánh chính tại trung tâm thành phố với không gian sang trọng, hiện đại. Cung cấp đầy đủ các dịch vụ chăm sóc da mặt và làm đẹp.',
                    'en' => 'Main branch in the city center with luxury and modern space. Provides full range of facial care and beauty services.',
                ],
                'amenities' => ['WiFi', 'Parking', 'Air Conditioning', 'Cash Payment', 'Credit Card', 'Digital Payment'],
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => [
                    'vi' => 'Spa & Beauty Center - Chi nhánh Quận 2',
                    'en' => 'Spa & Beauty Center - District 2 Branch',
                ],
                'slug' => 'spa-beauty-center-quan-2',
                'address' => [
                    'vi' => '456 Đại lộ Nguyễn Văn Linh, Thảo Điền, Quận 2, TP. Hồ Chí Minh',
                    'en' => '456 Nguyen Van Linh Boulevard, Thao Dien, District 2, Ho Chi Minh City',
                ],
                'phone' => '028 3748 5678',
                'email' => 'branch2@example.com',
                'latitude' => 10.8030,
                'longitude' => 106.7418,
                'opening_hours' => [
                    'monday' => ['09:00', '19:00'],
                    'tuesday' => ['09:00', '19:00'],
                    'wednesday' => ['09:00', '19:00'],
                    'thursday' => ['09:00', '19:00'],
                    'friday' => ['09:00', '19:00'],
                    'saturday' => ['09:00', '19:00'],
                    'sunday' => ['10:00', '18:00'],
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1598816284472-0e5e8f7fe0af?w=800&h=600',
                    'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800&h=600',
                ],
                'description' => [
                    'vi' => 'Chi nhánh tại khu vực cao cấp Thảo Điền với không gian yên tĩnh, sang trọng. Chuyên về các liệu pháp chăm sóc da cao cấp.',
                    'en' => 'Branch in high-end Thao Dien area with quiet and elegant space. Specialized in premium skin care treatments.',
                ],
                'amenities' => ['WiFi', 'Parking', 'Air Conditioning', 'Credit Card', 'Private Room', 'VIP Service'],
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => [
                    'vi' => 'Spa & Beauty Center - Chi nhánh Quận 7',
                    'en' => 'Spa & Beauty Center - District 7 Branch',
                ],
                'slug' => 'spa-beauty-center-quan-7',
                'address' => [
                    'vi' => '789 Phạm Hùng, Phường 5, Quận 7, TP. Hồ Chí Minh',
                    'en' => '789 Pham Hung Street, Ward 5, District 7, Ho Chi Minh City',
                ],
                'phone' => '028 3837 9012',
                'email' => 'branch3@example.com',
                'latitude' => 10.7327,
                'longitude' => 106.7076,
                'opening_hours' => [
                    'monday' => ['08:00', '20:00'],
                    'tuesday' => ['08:00', '20:00'],
                    'wednesday' => ['08:00', '20:00'],
                    'thursday' => ['08:00', '20:00'],
                    'friday' => ['08:00', '20:00'],
                    'saturday' => ['08:00', '20:00'],
                    'sunday' => ['09:00', '19:00'],
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800&h=600',
                    'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=800&h=600',
                ],
                'description' => [
                    'vi' => 'Chi nhánh hiện đại tại khu Nam Sài Gòn với giá cả hợp lý, phục vụ tốt cho cộng đồng địa phương.',
                    'en' => 'Modern branch in South Saigon with reasonable prices, serving the local community well.',
                ],
                'amenities' => ['WiFi', 'Parking', 'Air Conditioning', 'Cash Payment', 'Credit Card', 'Momo', 'ZaloPay'],
                'is_active' => true,
                'display_order' => 3,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }

        // Tạo thêm 2 chi nhánh với factory
        Branch::factory()->count(2)->create();
    }
}

