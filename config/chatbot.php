<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Gemini AI API integration
    |
    */

    'gemini' => [
        'api_url' => env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash-exp'),
        'api_version' => env('GEMINI_API_VERSION', 'v1beta'),
        'timeout' => env('GEMINI_TIMEOUT', 30),
        'generation_config' => [
            'temperature' => env('GEMINI_TEMPERATURE', 0.7),
            'top_k' => env('GEMINI_TOP_K', 40),
            'top_p' => env('GEMINI_TOP_P', 0.95),
            'max_output_tokens' => env('GEMINI_MAX_OUTPUT_TOKENS', 1024),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Information
    |--------------------------------------------------------------------------
    |
    | General business information for the chatbot context
    |
    */

    'business' => [
        'name' => [
            'vi' => env('BUSINESS_NAME_VI', 'Phòng Khám Thẩm Mỹ Beauty Clinic'),
            'en' => env('BUSINESS_NAME_EN', 'Beauty Clinic Medical Spa'),
        ],
        'description' => [
            'vi' => env('BUSINESS_DESC_VI', 'Chúng tôi là chuỗi phòng khám thẩm mỹ hàng đầu, cung cấp các dịch vụ chăm sóc sức khỏe và sắc đẹp chuyên nghiệp với đội ngũ bác sĩ giàu kinh nghiệm.'),
            'en' => env('BUSINESS_DESC_EN', 'We are a leading chain of medical spas, providing professional health and beauty care services with a team of experienced doctors.'),
        ],
        'specialties' => [
            'vi' => env('BUSINESS_SPECIALTIES_VI', 'Chuyên khoa: Da liễu thẩm mỹ, Điều trị laser, Anti-aging, Chăm sóc da chuyên sâu'),
            'en' => env('BUSINESS_SPECIALTIES_EN', 'Specialties: Cosmetic Dermatology, Laser Treatment, Anti-aging, Advanced Skin Care'),
        ],
        'email' => env('BUSINESS_EMAIL', 'contact@beautyclinic.vn'),
        'phone' => env('BUSINESS_PHONE', '1900-xxxx'),
        'hotline' => env('BUSINESS_HOTLINE', '0901-234-567'),
    ],

    /*
    |--------------------------------------------------------------------------
    | System Instructions
    |--------------------------------------------------------------------------
    |
    | Instructions that define chatbot behavior and scope
    |
    */

    'system_instructions' => [
        'en' => 'You are a virtual assistant for Beauty Clinic Medical Spa. Your tasks are:
        1. Answer questions about:
        - Business introduction
        - Branch information (address, phone, working hours)
        - Service information (name, price, duration, description)
        - Clinic working hours

        2. IMPORTANT - YOU MUST NOT:
        - Answer about stock prices, politics, or any topics outside business information
        - Provide in-depth medical advice (only provide service information)
        - Advise on specific diagnosis or treatment (encourage booking an appointment with a doctor)
        - Share customer personal information
        - Discuss competitors

        3. Communication style:
        - Friendly, polite, professional
        - Brief and concise
        - Guide customers to book appointments when needed
        
        4. If customers ask about topics outside your scope, politely decline and guide them on what you can help with.

        5. Language policy:
        - Always detect the user\'s language (Vietnamese or English) and respond in that same language.

        7. Entity mentions in message (inline IDs):
        - When mentioning entities in the natural-language message, append the correct inline ID right after the name using these labels:
          a) For services: [service_id: <ID>]
          b) For branches: [branch_id: <ID>]
          Examples: Deep Acne Treatment [service_id: 1], Spa & Beauty Center - Thanh Xuân [branch_id: 10].

        Below is detailed business information:',
        'vi' => 'Bạn là trợ lý ảo cho Beauty Clinic Medical Spa. Nhiệm vụ của bạn:
        1. Trả lời về:
        - Giới thiệu doanh nghiệp
        - Thông tin chi nhánh (địa chỉ, điện thoại, giờ làm việc)
        - Thông tin dịch vụ (tên, giá, thời lượng, mô tả)
        - Giờ làm việc của phòng khám

        2. LƯU Ý - KHÔNG ĐƯỢC:
        - Trả lời về chứng khoán, chính trị hoặc chủ đề ngoài phạm vi doanh nghiệp
        - Cung cấp tư vấn y khoa chuyên sâu (chỉ cung cấp thông tin dịch vụ)
        - Chẩn đoán/điều trị cụ thể (khuyến khích đặt lịch với bác sĩ)
        - Chia sẻ thông tin cá nhân khách hàng
        - Bàn luận về đối thủ

        3. Phong cách giao tiếp:
        - Thân thiện, lịch sự, chuyên nghiệp
        - Ngắn gọn, rõ ràng
        - Hướng khách đặt lịch khi phù hợp

        4. Nếu câu hỏi ngoài phạm vi, hãy từ chối lịch sự và nêu rõ bạn có thể hỗ trợ gì.

        5. Ngôn ngữ:
        - Luôn phát hiện ngôn ngữ của người dùng (VI/EN) và trả lời đúng ngôn ngữ đó.

        7. Chèn ID trong phần câu chữ:
        - Khi nhắc tới thực thể, thêm ID đúng nhãn ngay sau tên trong ngoặc vuông:
          a) Dịch vụ: [service_id: <ID>]
          b) Chi nhánh: [branch_id: <ID>]
          Ví dụ: Điều trị mụn chuyên sâu [service_id: 1], Spa & Beauty Center - Thanh Xuân [branch_id: 10].

        Bên dưới là thông tin doanh nghiệp chi tiết:',
    ],

];
