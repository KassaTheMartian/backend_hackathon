<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\User;
use App\Models\Branch;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();

        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please run BranchSeeder first.');
            return;
        }

        // Lấy các users không phải admin để làm staff
        $users = User::where('is_admin', false)->get();

        // Tạo staff mẫu có dữ liệu cụ thể - tạo User trước, sau đó tạo Staff gắn user_id
        $samples = [
            [
                'user' => ['name' => 'Nguyễn Thị Lan', 'email' => 'staff1@example.com', 'phone' => '0901111222', 'avatar' => 'https://ui-avatars.com/api/?name=Nguyen+Thi+Lan&background=random'],
                'staff' => [
                    'position' => 'Senior Therapist',
                    'specialization' => ['Facial Treatment', 'Acne Treatment', 'Skin Whitening'],
                    'bio' => [
                        'vi' => 'Chuyên gia chăm sóc da với 10 năm kinh nghiệm. Đã được đào tạo về các công nghệ làm đẹp tiên tiến.',
                        'en' => 'Skin care expert with 10 years of experience. Trained in advanced beauty technologies.',
                        'ja' => '10年の経験を持つスキンケア専門家。先進的な美容技術を習得。',
                        'zh' => '拥有10年经验的皮肤护理专家，受过先进美容技术培训。',
                    ],
                    'years_of_experience' => 10,
                    'certifications' => ['Advanced Facial Care', 'Laser Treatment', 'Acne Specialist'],
                    'rating' => 4.9,
                    'total_reviews' => 156,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Trần Văn Minh', 'email' => 'staff2@example.com', 'phone' => '0902222333', 'avatar' => 'https://ui-avatars.com/api/?name=Tran+Van+Minh&background=random'],
                'staff' => [
                    'position' => 'Senior Therapist',
                    'specialization' => ['Anti-Aging', 'Facial Treatment'],
                    'bio' => [
                        'vi' => 'Chuyên gia chống lão hóa với kinh nghiệm lâu năm trong việc điều trị các vấn đề về da.',
                        'en' => 'Anti-aging specialist with years of experience in treating skin problems.',
                        'ja' => '豊富な経験を持つアンチエイジングのスペシャリスト。',
                        'zh' => '拥有多年经验的抗衰老专家。',
                    ],
                    'years_of_experience' => 8,
                    'certifications' => ['Anti-Aging Specialist', 'Collagen Therapy'],
                    'rating' => 4.8,
                    'total_reviews' => 134,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Lê Thị Hương', 'email' => 'staff3@example.com', 'phone' => '0903333444', 'avatar' => 'https://ui-avatars.com/api/?name=Le+Thi+Huong&background=random'],
                'staff' => [
                    'position' => 'Junior Therapist',
                    'specialization' => ['Facial Treatment', 'Basic Care'],
                    'bio' => [
                        'vi' => 'Nhân viên chăm sóc da trẻ trung, nhiệt tình với khách hàng.',
                        'en' => 'Young and enthusiastic skin care staff, dedicated to customer service.',
                        'ja' => '若くて情熱的なスキンケアスタッフ。',
                        'zh' => '年轻且充满热情的皮肤护理员工。',
                    ],
                    'years_of_experience' => 2,
                    'certifications' => ['Basic Facial Care'],
                    'rating' => 4.5,
                    'total_reviews' => 67,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Phạm Văn Đức', 'email' => 'staff4@example.com', 'phone' => '0904444555', 'avatar' => 'https://ui-avatars.com/api/?name=Pham+Van+Duc&background=random'],
                'staff' => [
                    'position' => 'Manager',
                    'specialization' => ['Facial Treatment', 'Acne Treatment', 'Anti-Aging'],
                    'bio' => [
                        'vi' => 'Quản lý chi nhánh với 15 năm kinh nghiệm trong ngành làm đẹp.',
                        'en' => 'Branch manager with 15 years of experience in the beauty industry.',
                        'ja' => '美容業界で15年の経験を持つ支店マネージャー。',
                        'zh' => '拥有15年美容行业经验的分店经理。',
                    ],
                    'years_of_experience' => 15,
                    'certifications' => ['Beauty Management', 'Advanced Skin Care', 'Business Administration'],
                    'rating' => 5.0,
                    'total_reviews' => 89,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Hoàng Thị Mai', 'email' => 'staff5@example.com', 'phone' => '0905555666', 'avatar' => 'https://ui-avatars.com/api/?name=Hoang+Thi+Mai&background=random'],
                'staff' => [
                    'position' => 'Receptionist',
                    'specialization' => ['Customer Service'],
                    'bio' => [
                        'vi' => 'Thiên thần phục vụ khách hàng, luôn niềm nở và chuyên nghiệp.',
                        'en' => 'Customer service angel, always friendly and professional.',
                        'ja' => 'お客様対応に優れ、いつも親切でプロフェッショナル。',
                        'zh' => '客户服务天使，始终友好且专业。',
                    ],
                    'years_of_experience' => 5,
                    'certifications' => ['Customer Service Excellence'],
                    'rating' => 4.7,
                    'total_reviews' => 45,
                    'is_active' => true,
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $user = User::factory()->create([
                'name' => $sample['user']['name'],
                'email' => $sample['user']['email'],
                'phone' => $sample['user']['phone'],
                'avatar' => $sample['user']['avatar'],
                'is_active' => true,
            ]);

            Staff::create(array_merge($sample['staff'], [
                'user_id' => $user->id,
                'branch_id' => $branches->random()->id,
            ]));
        }
    }
}

