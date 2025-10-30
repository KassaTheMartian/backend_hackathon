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
                    'ja' => 'スパ＆ビューティーセンター - 第1区支店',
                    'zh' => '水疗美容中心 - 第一区分店',
                ],
                'slug' => 'spa-beauty-center-quan-1',
                'address' => [
                    'vi' => '123 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP. Hồ Chí Minh',
                    'en' => '123 Nguyen Hue Street, Ben Nghe Ward, District 1, Ho Chi Minh City',
                    'ja' => 'ホーチミン市1区ベンゲー区グエンフエ123番地',
                    'zh' => '胡志明市第一郡本艺坊阮惠街123号',
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
                    'ja' => '市内中心部にある本店は高級でモダンな空間。フェイシャルケアと美容サービスを幅広く提供します。',
                    'zh' => '位于市中心的主分店，环境高档现代，提供全方位面部护理和美容服务。',
                ],
                'amenities' => ['WiFi', 'Parking', 'Air Conditioning', 'Cash Payment', 'Credit Card', 'Digital Payment'],
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => [
                    'vi' => 'Spa & Beauty Center - Chi nhánh Quận 2',
                    'en' => 'Spa & Beauty Center - District 2 Branch',
                    'ja' => 'スパ＆ビューティーセンター - 第2区支店',
                    'zh' => '水疗美容中心 - 第二区分店',
                ],
                'slug' => 'spa-beauty-center-quan-2',
                'address' => [
                    'vi' => '456 Đại lộ Nguyễn Văn Linh, Thảo Điền, Quận 2, TP. Hồ Chí Minh',
                    'en' => '456 Nguyen Van Linh Boulevard, Thao Dien, District 2, Ho Chi Minh City',
                    'ja' => 'ホーチミン市2区タオディエン、グエンヴァンリン大通り456番地',
                    'zh' => '胡志明市第二郡守添坊阮文灵大道456号',
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
                    'ja' => '高級住宅地タオディエンにある支店。静かでエレガントな空間で高級スキンケアに特化。',
                    'zh' => '位于高端的守添地区，环境静谧优雅，专注高端皮肤护理疗程。',
                ],
                'amenities' => ['WiFi', 'Parking', 'Air Conditioning', 'Credit Card', 'Private Room', 'VIP Service'],
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => [
                    'vi' => 'Spa & Beauty Center - Chi nhánh Quận 7',
                    'en' => 'Spa & Beauty Center - District 7 Branch',
                    'ja' => 'スパ＆ビューティーセンター - 第7区支店',
                    'zh' => '水疗美容中心 - 第七区分店',
                ],
                'slug' => 'spa-beauty-center-quan-7',
                'address' => [
                    'vi' => '789 Phạm Hùng, Phường 5, Quận 7, TP. Hồ Chí Minh',
                    'en' => '789 Pham Hung Street, Ward 5, District 7, Ho Chi Minh City',
                    'ja' => 'ホーチミン市7区5坊ファムフン通り789番地',
                    'zh' => '胡志明市第七郡第五坊范雄街789号',
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
                    'ja' => 'サイゴン南部にあるモダンな支店。手頃な価格で地域コミュニティに貢献。',
                    'zh' => '位于西贡南部的现代化分店，价格合理，服务本地社区。',
                ],
                'amenities' => ['WiFi', 'Parking', 'Air Conditioning', 'Cash Payment', 'Credit Card', 'Momo', 'ZaloPay'],
                'is_active' => true,
                'display_order' => 3,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
}

