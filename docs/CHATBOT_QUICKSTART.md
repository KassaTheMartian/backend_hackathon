# Quick Start - Test Chatbot API

## 1. Cấu hình API Key

Thêm vào file `.env`:
```env
GEMINI_API_KEY=your_api_key_here
```

Lấy API key tại: https://makersuite.google.com/app/apikey

## 2. Tùy chỉnh cấu hình (Optional)

Bạn có thể tùy chỉnh thêm trong `.env`:
```env
# Gemini API Settings
GEMINI_TIMEOUT=30
GEMINI_TEMPERATURE=0.7
GEMINI_MAX_OUTPUT_TOKENS=1024

# Business Information
BUSINESS_NAME_VI="Tên phòng khám của bạn"
BUSINESS_EMAIL=youremail@example.com
BUSINESS_PHONE=1900-xxxx
```

Hoặc chỉnh sửa trực tiếp trong `config/chatbot.php`

## 3. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## 3. Test với cURL

### Tiếng Việt
```bash
curl -X POST http://localhost:8000/api/v1/chatbot \
  -H "Content-Type: application/json" \
  -H "Accept-Language: vi" \
  -d "{\"message\":\"Cho tôi biết thông tin về phòng khám\"}"
```

### English
```bash
curl -X POST http://localhost:8000/api/v1/chatbot \
  -H "Content-Type: application/json" \
  -H "Accept-Language: en" \
  -d "{\"message\":\"Tell me about your services\"}"
```

## 4. Test Questions

### Tiếng Việt
- "Cho tôi biết thông tin về phòng khám"
- "Các chi nhánh ở đâu?"
- "Dịch vụ chăm sóc da giá bao nhiêu?"
- "Giờ làm việc là mấy giờ?"
- "Địa chỉ chi nhánh gần nhất"

### English
- "Tell me about your clinic"
- "Where are your branches?"
- "How much does skin care cost?"
- "What are your working hours?"
- "List all your services"

## 5. Expected Response

```json
{
  "success": true,
  "message": "Tạo phản hồi thành công",
  "data": {
    "message": "AI response here...",
    "user_id": null,
    "locale": "vi"
  }
}
```

## 6. Check Errors

If you get errors, check:
- ✅ `.env` has `GEMINI_API_KEY`
- ✅ API key is valid
- ✅ Internet connection
- ✅ Cache cleared

## Files Created

- `app/Repositories/Contracts/ChatbotRepositoryInterface.php`
- `app/Repositories/Eloquent/ChatbotRepository.php`
- `app/Services/Contracts/ChatbotServiceInterface.php`
- `app/Services/ChatbotService.php`
- `app/Http/Controllers/Api/V1/ChatbotController.php`
- `app/Http/Requests/Chatbot/ChatRequest.php`
- Updated: `routes/api.php`
- Updated: `app/Providers/AppServiceProvider.php`
- Updated: `resources/lang/en/chatbot.php`
- Updated: `resources/lang/vi/chatbot.php`
- Updated: `.env.example`
