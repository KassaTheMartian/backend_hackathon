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
            [
                'user' => ['name' => 'Vũ Thị Linh', 'email' => 'staff6@example.com', 'phone' => '0906666777', 'avatar' => 'https://ui-avatars.com/api/?name=Vu+Thi+Linh&background=random'],
                'staff' => [
                    'position' => 'Senior Therapist',
                    'specialization' => ['Body Massage', 'Relaxation Therapy'],
                    'bio' => [
                        'vi' => 'Chuyên gia massage cơ thể với kỹ thuật thư giãn chuyên nghiệp.',
                        'en' => 'Body massage expert with professional relaxation techniques.',
                        'ja' => 'プロフェッショナルなリラクゼーション技術を持つボディマッサージ専門家。',
                        'zh' => '拥有专业放松技术的身体按摩专家。',
                    ],
                    'years_of_experience' => 12,
                    'certifications' => ['Advanced Massage Therapy', 'Relaxation Specialist'],
                    'rating' => 4.9,
                    'total_reviews' => 178,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Đỗ Văn Tùng', 'email' => 'staff7@example.com', 'phone' => '0907777888', 'avatar' => 'https://ui-avatars.com/api/?name=Do+Van+Tung&background=random'],
                'staff' => [
                    'position' => 'Therapist',
                    'specialization' => ['Hair Care', 'Scalp Treatment'],
                    'bio' => [
                        'vi' => 'Chuyên gia chăm sóc tóc và da đầu với phương pháp tự nhiên.',
                        'en' => 'Hair care specialist using natural methods.',
                        'ja' => '自然療法を用いたヘアケア専門家。',
                        'zh' => '使用自然方法的头发护理专家。',
                    ],
                    'years_of_experience' => 7,
                    'certifications' => ['Hair Care Specialist', 'Natural Therapy'],
                    'rating' => 4.6,
                    'total_reviews' => 92,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Ngô Thị Hoa', 'email' => 'staff8@example.com', 'phone' => '0908888999', 'avatar' => 'https://ui-avatars.com/api/?name=Ngo+Thi+Hoa&background=random'],
                'staff' => [
                    'position' => 'Junior Therapist',
                    'specialization' => ['Basic Facial', 'Eye Care'],
                    'bio' => [
                        'vi' => 'Nhân viên chăm sóc mắt và mặt cơ bản, tận tâm với công việc.',
                        'en' => 'Dedicated staff for basic facial and eye care.',
                        'ja' => '基本的なフェイシャルとアイケアに専念するスタッフ。',
                        'zh' => '致力于基本面部和眼部护理的员工。',
                    ],
                    'years_of_experience' => 3,
                    'certifications' => ['Basic Eye Care'],
                    'rating' => 4.4,
                    'total_reviews' => 58,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Bùi Văn Sơn', 'email' => 'staff9@example.com', 'phone' => '0909999000', 'avatar' => 'https://ui-avatars.com/api/?name=Bui+Van+Son&background=random'],
                'staff' => [
                    'position' => 'Senior Therapist',
                    'specialization' => ['Laser Treatment', 'Skin Rejuvenation'],
                    'bio' => [
                        'vi' => 'Chuyên gia laser và tái tạo da với công nghệ tiên tiến.',
                        'en' => 'Laser and skin rejuvenation expert with advanced technology.',
                        'ja' => '先進技術を持つレーザーとスキンリジュベネーションの専門家。',
                        'zh' => '拥有先进技术的激光和皮肤再生专家。',
                    ],
                    'years_of_experience' => 11,
                    'certifications' => ['Laser Specialist', 'Skin Rejuvenation'],
                    'rating' => 4.8,
                    'total_reviews' => 145,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Trịnh Thị Nga', 'email' => 'staff10@example.com', 'phone' => '0910000111', 'avatar' => 'https://ui-avatars.com/api/?name=Trinh+Thi+Nga&background=random'],
                'staff' => [
                    'position' => 'Therapist',
                    'specialization' => ['Nail Care', 'Manicure'],
                    'bio' => [
                        'vi' => 'Chuyên gia chăm sóc móng tay với thiết kế sáng tạo.',
                        'en' => 'Nail care specialist with creative designs.',
                        'ja' => '創造的なデザインを持つネイルケア専門家。',
                        'zh' => '拥有创意设计的指甲护理专家。',
                    ],
                    'years_of_experience' => 6,
                    'certifications' => ['Nail Art Specialist'],
                    'rating' => 4.7,
                    'total_reviews' => 103,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Lý Văn Hùng', 'email' => 'staff11@example.com', 'phone' => '0911111222', 'avatar' => 'https://ui-avatars.com/api/?name=Ly+Van+Hung&background=random'],
                'staff' => [
                    'position' => 'Manager',
                    'specialization' => ['Team Management', 'Customer Relations'],
                    'bio' => [
                        'vi' => 'Quản lý đội ngũ với kỹ năng quản lý và giao tiếp xuất sắc.',
                        'en' => 'Team manager with excellent management and communication skills.',
                        'ja' => '優れたマネジメントとコミュニケーションスキルを持つチームマネージャー。',
                        'zh' => '拥有出色管理和沟通技能的团队经理。',
                    ],
                    'years_of_experience' => 14,
                    'certifications' => ['Team Leadership', 'Customer Relations'],
                    'rating' => 4.9,
                    'total_reviews' => 76,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Đinh Thị Lan', 'email' => 'staff12@example.com', 'phone' => '0912222333', 'avatar' => 'https://ui-avatars.com/api/?name=Dinh+Thi+Lan&background=random'],
                'staff' => [
                    'position' => 'Receptionist',
                    'specialization' => ['Customer Service', 'Appointment Scheduling'],
                    'bio' => [
                        'vi' => 'Lễ tân chuyên nghiệp, hỗ trợ đặt lịch và chăm sóc khách hàng.',
                        'en' => 'Professional receptionist handling appointments and customer care.',
                        'ja' => '予約と顧客ケアを扱うプロフェッショナルな受付。',
                        'zh' => '处理预约和客户服务的专业接待员。',
                    ],
                    'years_of_experience' => 4,
                    'certifications' => ['Appointment Management'],
                    'rating' => 4.6,
                    'total_reviews' => 62,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Mai Văn Khoa', 'email' => 'staff13@example.com', 'phone' => '0913333444', 'avatar' => 'https://ui-avatars.com/api/?name=Mai+Van+Khoa&background=random'],
                'staff' => [
                    'position' => 'Senior Therapist',
                    'specialization' => ['Aromatherapy', 'Essential Oils'],
                    'bio' => [
                        'vi' => 'Chuyên gia hương liệu với kiến thức sâu về tinh dầu.',
                        'en' => 'Aromatherapy expert with deep knowledge of essential oils.',
                        'ja' => 'エッセンシャルオイルの深い知識を持つアロマセラピー専門家。',
                        'zh' => '拥有精油深厚知识的芳香疗法专家。',
                    ],
                    'years_of_experience' => 9,
                    'certifications' => ['Aromatherapy Specialist', 'Essential Oils'],
                    'rating' => 4.8,
                    'total_reviews' => 127,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Tô Thị Thu', 'email' => 'staff14@example.com', 'phone' => '0914444555', 'avatar' => 'https://ui-avatars.com/api/?name=To+Thi+Thu&background=random'],
                'staff' => [
                    'position' => 'Therapist',
                    'specialization' => ['Makeup', 'Bridal Makeup'],
                    'bio' => [
                        'vi' => 'Chuyên gia trang điểm, đặc biệt là trang điểm cô dâu.',
                        'en' => 'Makeup specialist, especially bridal makeup.',
                        'ja' => '特にブライダルメイクのメイクアップ専門家。',
                        'zh' => '化妆专家，特别是新娘化妆。',
                    ],
                    'years_of_experience' => 8,
                    'certifications' => ['Bridal Makeup Artist'],
                    'rating' => 4.9,
                    'total_reviews' => 189,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Hà Văn Nam', 'email' => 'staff15@example.com', 'phone' => '0915555666', 'avatar' => 'https://ui-avatars.com/api/?name=Ha+Van+Nam&background=random'],
                'staff' => [
                    'position' => 'Junior Therapist',
                    'specialization' => ['Basic Massage', 'Foot Care'],
                    'bio' => [
                        'vi' => 'Nhân viên massage cơ bản và chăm sóc chân.',
                        'en' => 'Staff for basic massage and foot care.',
                        'ja' => '基本的なマッサージとフットケアのスタッフ。',
                        'zh' => '基本按摩和足部护理员工。',
                    ],
                    'years_of_experience' => 2,
                    'certifications' => ['Basic Massage'],
                    'rating' => 4.3,
                    'total_reviews' => 41,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Nguyễn Văn Bình', 'email' => 'staff16@example.com', 'phone' => '0916666777', 'avatar' => 'https://ui-avatars.com/api/?name=Nguyen+Van+Binh&background=random'],
                'staff' => [
                    'position' => 'Senior Therapist',
                    'specialization' => ['Waxing', 'Hair Removal'],
                    'bio' => [
                        'vi' => 'Chuyên gia waxing và loại bỏ lông với kỹ thuật an toàn.',
                        'en' => 'Waxing and hair removal expert with safe techniques.',
                        'ja' => '安全な技術を持つワックスと脱毛の専門家。',
                        'zh' => '拥有安全技术的蜡脱和脱毛专家。',
                    ],
                    'years_of_experience' => 10,
                    'certifications' => ['Waxing Specialist', 'Hair Removal'],
                    'rating' => 4.7,
                    'total_reviews' => 112,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Trần Thị Dung', 'email' => 'staff17@example.com', 'phone' => '0917777888', 'avatar' => 'https://ui-avatars.com/api/?name=Tran+Thi+Dung&background=random'],
                'staff' => [
                    'position' => 'Therapist',
                    'specialization' => ['Eyelash Extension', 'Eyebrow Shaping'],
                    'bio' => [
                        'vi' => 'Chuyên gia nối mi và tạo hình lông mày.',
                        'en' => 'Eyelash extension and eyebrow shaping specialist.',
                        'ja' => 'まつげエクステと眉毛形成の専門家。',
                        'zh' => '睫毛延长和眉毛造型专家。',
                    ],
                    'years_of_experience' => 5,
                    'certifications' => ['Eyelash Extension'],
                    'rating' => 4.8,
                    'total_reviews' => 98,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Lê Văn Quang', 'email' => 'staff18@example.com', 'phone' => '0918888999', 'avatar' => 'https://ui-avatars.com/api/?name=Le+Van+Quang&background=random'],
                'staff' => [
                    'position' => 'Manager',
                    'specialization' => ['Operations', 'Quality Control'],
                    'bio' => [
                        'vi' => 'Quản lý vận hành với trọng tâm kiểm soát chất lượng.',
                        'en' => 'Operations manager focused on quality control.',
                        'ja' => '品質管理に焦点を当てたオペレーションマネージャー。',
                        'zh' => '专注于质量控制的操作经理。',
                    ],
                    'years_of_experience' => 13,
                    'certifications' => ['Quality Management', 'Operations'],
                    'rating' => 4.9,
                    'total_reviews' => 84,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Phạm Thị Hà', 'email' => 'staff19@example.com', 'phone' => '0919999000', 'avatar' => 'https://ui-avatars.com/api/?name=Pham+Thi+Ha&background=random'],
                'staff' => [
                    'position' => 'Receptionist',
                    'specialization' => ['Customer Service', 'Billing'],
                    'bio' => [
                        'vi' => 'Lễ tân xử lý thanh toán và dịch vụ khách hàng.',
                        'en' => 'Receptionist handling billing and customer service.',
                        'ja' => '請求と顧客サービスを扱う受付。',
                        'zh' => '处理账单和客户服务的接待员。',
                    ],
                    'years_of_experience' => 6,
                    'certifications' => ['Billing Specialist'],
                    'rating' => 4.5,
                    'total_reviews' => 73,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Hoàng Văn Tâm', 'email' => 'staff20@example.com', 'phone' => '0920000111', 'avatar' => 'https://ui-avatars.com/api/?name=Hoang+Van+Tam&background=random'],
                'staff' => [
                    'position' => 'Senior Therapist',
                    'specialization' => ['Acupuncture', 'Traditional Medicine'],
                    'bio' => [
                        'vi' => 'Chuyên gia châm cứu và y học cổ truyền.',
                        'en' => 'Acupuncture and traditional medicine expert.',
                        'ja' => '鍼灸と伝統医学の専門家。',
                        'zh' => '针灸和传统医学专家。',
                    ],
                    'years_of_experience' => 16,
                    'certifications' => ['Acupuncture Specialist', 'Traditional Medicine'],
                    'rating' => 4.9,
                    'total_reviews' => 201,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Vũ Văn Long', 'email' => 'staff21@example.com', 'phone' => '0921111222', 'avatar' => 'https://ui-avatars.com/api/?name=Vu+Van+Long&background=random'],
                'staff' => [
                    'position' => 'Therapist',
                    'specialization' => ['Reflexology', 'Foot Massage'],
                    'bio' => [
                        'vi' => 'Chuyên gia phản xạ và massage chân.',
                        'en' => 'Reflexology and foot massage specialist.',
                        'ja' => 'リフレクソロジーとフットマッサージの専門家。',
                        'zh' => '反射疗法和足部按摩专家。',
                    ],
                    'years_of_experience' => 7,
                    'certifications' => ['Reflexology'],
                    'rating' => 4.6,
                    'total_reviews' => 115,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Đỗ Thị Mai', 'email' => 'staff22@example.com', 'phone' => '0922222333', 'avatar' => 'https://ui-avatars.com/api/?name=Do+Thi+Mai&background=random'],
                'staff' => [
                    'position' => 'Junior Therapist',
                    'specialization' => ['Basic Skincare', 'Cleansing'],
                    'bio' => [
                        'vi' => 'Nhân viên chăm sóc da cơ bản và làm sạch.',
                        'en' => 'Staff for basic skincare and cleansing.',
                        'ja' => '基本的なスキンケアとクレンジングのスタッフ。',
                        'zh' => '基本皮肤护理和清洁员工。',
                    ],
                    'years_of_experience' => 1,
                    'certifications' => ['Basic Skincare'],
                    'rating' => 4.2,
                    'total_reviews' => 29,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Ngô Văn Hải', 'email' => 'staff23@example.com', 'phone' => '0923333444', 'avatar' => 'https://ui-avatars.com/api/?name=Ngo+Van+Hai&background=random'],
                'staff' => [
                    'position' => 'Senior Therapist',
                    'specialization' => ['Hydrotherapy', 'Water Treatments'],
                    'bio' => [
                        'vi' => 'Chuyên gia thủy trị liệu và các liệu pháp nước.',
                        'en' => 'Hydrotherapy and water treatments expert.',
                        'ja' => 'ハイドロセラピーとウォータートリートメントの専門家。',
                        'zh' => '水疗法和水处理专家。',
                    ],
                    'years_of_experience' => 12,
                    'certifications' => ['Hydrotherapy Specialist'],
                    'rating' => 4.8,
                    'total_reviews' => 167,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Bùi Thị Linh', 'email' => 'staff24@example.com', 'phone' => '0924444555', 'avatar' => 'https://ui-avatars.com/api/?name=Bui+Thi+Linh&background=random'],
                'staff' => [
                    'position' => 'Therapist',
                    'specialization' => ['Tattoo Removal', 'Laser Procedures'],
                    'bio' => [
                        'vi' => 'Chuyên gia loại bỏ hình xăm bằng laser.',
                        'en' => 'Tattoo removal specialist using laser procedures.',
                        'ja' => 'レーザー手順を用いたタトゥー除去専門家。',
                        'zh' => '使用激光程序的纹身去除专家。',
                    ],
                    'years_of_experience' => 9,
                    'certifications' => ['Laser Tattoo Removal'],
                    'rating' => 4.7,
                    'total_reviews' => 134,
                    'is_active' => true,
                ],
            ],
            [
                'user' => ['name' => 'Trịnh Văn Đức', 'email' => 'staff25@example.com', 'phone' => '0925555666', 'avatar' => 'https://ui-avatars.com/api/?name=Trinh+Van+Duc&background=random'],
                'staff' => [
                    'position' => 'Manager',
                    'specialization' => ['Marketing', 'Client Acquisition'],
                    'bio' => [
                        'vi' => 'Quản lý marketing và thu hút khách hàng.',
                        'en' => 'Marketing manager focused on client acquisition.',
                        'ja' => 'クライアント獲得に焦点を当てたマーケティングマネージャー。',
                        'zh' => '专注于客户获取的营销经理。',
                    ],
                    'years_of_experience' => 11,
                    'certifications' => ['Marketing Specialist', 'Client Relations'],
                    'rating' => 4.8,
                    'total_reviews' => 91,
                    'is_active' => true,
                ],
            ],
        ];

        // Ensure each branch has exactly 9 staff members
        foreach ($branches as $branch) {
            $shuffledSamples = $samples;
            shuffle($shuffledSamples);

            for ($i = 0; $i < 9; $i++) {
                $sample = $shuffledSamples[$i % count($shuffledSamples)];

                $user = User::factory()->create([
                    'name' => $sample['user']['name'],
                    'email' => preg_replace('/@/', "+b{$branch->id}s" . ($i + 1) . '@', $sample['user']['email'], 1),
                    'phone' => $sample['user']['phone'] . ($branch->id % 10) . ($i + 1),
                    'avatar' => $sample['user']['avatar'],
                    'is_active' => true,
                ]);

                Staff::create(array_merge($sample['staff'], [
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                ]));
            }
        }
    }
}

