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

        // Tạo staff mẫu có dữ liệu cụ thể
        $staffs = [
            [
                'user_id' => $users->random()->id,
                'branch_id' => $branches->random()->id,
                'name' => 'Nguyễn Thị Lan',
                'email' => 'staff1@example.com',
                'phone' => '0901111222',
                'position' => 'Senior Therapist',
                'specialization' => ['Facial Treatment', 'Acne Treatment', 'Skin Whitening'],
                'bio' => [
                    'vi' => 'Chuyên gia chăm sóc da với 10 năm kinh nghiệm. Đã được đào tạo về các công nghệ làm đẹp tiên tiến.',
                    'en' => 'Skin care expert with 10 years of experience. Trained in advanced beauty technologies.',
                ],
                'avatar' => 'https://ui-avatars.com/api/?name=Nguyen+Thi+Lan&background=random',
                'years_of_experience' => 10,
                'certifications' => ['Advanced Facial Care', 'Laser Treatment', 'Acne Specialist'],
                'rating' => 4.9,
                'total_reviews' => 156,
                'is_active' => true,
            ],
            [
                'user_id' => $users->random()->id,
                'branch_id' => $branches->random()->id,
                'name' => 'Trần Văn Minh',
                'email' => 'staff2@example.com',
                'phone' => '0902222333',
                'position' => 'Senior Therapist',
                'specialization' => ['Anti-Aging', 'Facial Treatment'],
                'bio' => [
                    'vi' => 'Chuyên gia chống lão hóa với kinh nghiệm lâu năm trong việc điều trị các vấn đề về da.',
                    'en' => 'Anti-aging specialist with years of experience in treating skin problems.',
                ],
                'avatar' => 'https://ui-avatars.com/api/?name=Tran+Van+Minh&background=random',
                'years_of_experience' => 8,
                'certifications' => ['Anti-Aging Specialist', 'Collagen Therapy'],
                'rating' => 4.8,
                'total_reviews' => 134,
                'is_active' => true,
            ],
            [
                'user_id' => $users->random()->id,
                'branch_id' => $branches->random()->id,
                'name' => 'Lê Thị Hương',
                'email' => 'staff3@example.com',
                'phone' => '0903333444',
                'position' => 'Junior Therapist',
                'specialization' => ['Facial Treatment', 'Basic Care'],
                'bio' => [
                    'vi' => 'Nhân viên chăm sóc da trẻ trung, nhiệt tình với khách hàng.',
                    'en' => 'Young and enthusiastic skin care staff, dedicated to customer service.',
                ],
                'avatar' => 'https://ui-avatars.com/api/?name=Le+Thi+Huong&background=random',
                'years_of_experience' => 2,
                'certifications' => ['Basic Facial Care'],
                'rating' => 4.5,
                'total_reviews' => 67,
                'is_active' => true,
            ],
            [
                'user_id' => $users->random()->id,
                'branch_id' => $branches->random()->id,
                'name' => 'Phạm Văn Đức',
                'email' => 'staff4@example.com',
                'phone' => '0904444555',
                'position' => 'Manager',
                'specialization' => ['Facial Treatment', 'Acne Treatment', 'Anti-Aging'],
                'bio' => [
                    'vi' => 'Quản lý chi nhánh với 15 năm kinh nghiệm trong ngành làm đẹp.',
                    'en' => 'Branch manager with 15 years of experience in the beauty industry.',
                ],
                'avatar' => 'https://ui-avatars.com/api/?name=Pham+Van+Duc&background=random',
                'years_of_experience' => 15,
                'certifications' => ['Beauty Management', 'Advanced Skin Care', 'Business Administration'],
                'rating' => 5.0,
                'total_reviews' => 89,
                'is_active' => true,
            ],
            [
                'user_id' => $users->random()->id,
                'branch_id' => $branches->random()->id,
                'name' => 'Hoàng Thị Mai',
                'email' => 'staff5@example.com',
                'phone' => '0905555666',
                'position' => 'Receptionist',
                'specialization' => ['Customer Service'],
                'bio' => [
                    'vi' => 'Thiên thần phục vụ khách hàng, luôn niềm nở và chuyên nghiệp.',
                    'en' => 'Customer service angel, always friendly and professional.',
                ],
                'avatar' => 'https://ui-avatars.com/api/?name=Hoang+Thi+Mai&background=random',
                'years_of_experience' => 5,
                'certifications' => ['Customer Service Excellence'],
                'rating' => 4.7,
                'total_reviews' => 45,
                'is_active' => true,
            ],
        ];

        foreach ($staffs as $staff) {
            Staff::create($staff);
        }

        // Tạo thêm 5 staffs với factory
        Staff::factory()->count(5)->create();
    }
}

