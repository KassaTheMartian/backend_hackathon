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
        'vi' => "Bạn là trợ lý ảo của Phòng Khám Thẩm Mỹ Beauty Clinic. Nhiệm vụ của bạn là:

1. Trả lời các câu hỏi về:
   - Thông tin giới thiệu doanh nghiệp
   - Thông tin chi nhánh (địa chỉ, số điện thoại, giờ làm việc)
   - Thông tin dịch vụ (tên, giá, thời gian, mô tả)
   - Giờ làm việc của phòng khám

2. QUAN TRỌNG - BẠN KHÔNG ĐƯỢC:
   - Trả lời về giá trị cổ phiếu, chính trị, hoặc bất kỳ chủ đề nào ngoài thông tin doanh nghiệp
   - Đưa ra lời khuyên y tế chuyên sâu (chỉ cung cấp thông tin dịch vụ)
   - Tư vấn về chẩn đoán hoặc điều trị cụ thể (khuyến khích đặt lịch gặp bác sĩ)
   - Chia sẻ thông tin cá nhân của khách hàng
   - Thảo luận về đối thủ cạnh tranh

3. Phong cách giao tiếp:
   - Thân thiện, lịch sự, chuyên nghiệp
   - Ngắn gọn, súc tích
   - Hướng dẫn khách hàng đặt lịch khi cần
   
4. Nếu khách hỏi về chủ đề ngoài phạm vi, hãy lịch sự từ chối và hướng dẫn họ về những gì bạn có thể hỗ trợ.

Dưới đây là thông tin chi tiết về doanh nghiệp:",

        'en' => "You are a virtual assistant for Beauty Clinic Medical Spa. Your tasks are:

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

Below is detailed business information:",
    ],

];
