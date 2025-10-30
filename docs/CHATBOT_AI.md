# AI Chatbot với Gemini API

## Tổng quan

Chức năng chatbot AI sử dụng Google Gemini API để trả lời các câu hỏi của khách hàng về:
- Thông tin giới thiệu doanh nghiệp
- Thông tin chi nhánh (địa chỉ, số điện thoại, giờ làm việc)
- Thông tin dịch vụ (tên, giá, thời gian, mô tả)
- Giờ làm việc của phòng khám

## Tính năng

✅ **Hỗ trợ đa ngôn ngữ**: Tự động phát hiện và phản hồi bằng ngôn ngữ phù hợp (vi/en)
✅ **Cho cả khách vãng lai và đã đăng nhập**: Không yêu cầu authentication
✅ **Giới hạn phạm vi**: Chỉ trả lời về thông tin doanh nghiệp, từ chối các câu hỏi ngoài phạm vi
✅ **An toàn**: Sử dụng safety settings của Gemini để lọc nội dung không phù hợp

## Cấu trúc Code

### 1. Repository Layer
- **Interface**: `app/Repositories/Contracts/ChatbotRepositoryInterface.php`
- **Implementation**: `app/Repositories/Eloquent/ChatbotRepository.php`
- **Nhiệm vụ**: Lấy thông tin doanh nghiệp, chi nhánh, dịch vụ từ database

### 2. Service Layer
- **Interface**: `app/Services/Contracts/ChatbotServiceInterface.php`
- **Implementation**: `app/Services/ChatbotService.php`
- **Nhiệm vụ**: 
  - Gọi Gemini API
  - Xây dựng context và system instruction
  - Xử lý response và error handling

### 3. Controller Layer
- **File**: `app/Http/Controllers/Api/V1/ChatbotController.php`
- **Route**: `POST /api/v1/chatbot`
- **Nhiệm vụ**: Nhận request, gọi service, trả về response

### 4. Validation
- **File**: `app/Http/Requests/Chatbot/ChatRequest.php`
- **Rules**:
  - `message`: required, string, min:1, max:1000

### 5. Language Files
- **English**: `resources/lang/en/chatbot.php`
- **Vietnamese**: `resources/lang/vi/chatbot.php`

## Cài đặt

### 1. Cấu hình Gemini API Key

Thêm vào file `.env`:
```env
GEMINI_API_KEY=your_gemini_api_key_here
```

**Cách lấy API Key:**
1. Truy cập: https://makersuite.google.com/app/apikey
2. Tạo API key mới
3. Copy và dán vào file `.env`

### 2. Đảm bảo Service Provider đã đăng ký

File `app/Providers/AppServiceProvider.php` đã tự động được cập nhật với:
```php
$this->app->bind(ChatbotRepositoryInterface::class, ChatbotRepository::class);
$this->app->bind(ChatbotServiceInterface::class, ChatbotService::class);
```

### 3. Clear cache (nếu cần)

```bash
php artisan config:clear
php artisan cache:clear
```

## API Endpoint

### POST `/api/v1/chatbot`

**Request:**
```json
{
  "message": "Tôi muốn biết thông tin về dịch vụ chăm sóc da"
}
```

**Headers:**
```
Accept-Language: vi  // hoặc en
Content-Type: application/json
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Tạo phản hồi thành công",
  "data": {
    "message": "Chúng tôi cung cấp các dịch vụ chăm sóc da chuyên sâu...",
    "user_id": 1,  // null nếu guest
    "locale": "vi"
  }
}
```

**Response Error (500):**
```json
{
  "success": false,
  "message": "Tạo phản hồi thất bại",
  "error_code": "CHATBOT_ERROR",
  "error": "Lỗi khi giao tiếp với dịch vụ AI"
}
```

**Response Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "message": ["Tin nhắn là bắt buộc"]
  }
}
```

## Ví dụ sử dụng

### 1. Với cURL

```bash
# Tiếng Việt
curl -X POST http://localhost:8000/api/v1/chatbot \
  -H "Content-Type: application/json" \
  -H "Accept-Language: vi" \
  -d '{"message":"Cho tôi biết thông tin về các chi nhánh"}'

# English
curl -X POST http://localhost:8000/api/v1/chatbot \
  -H "Content-Type: application/json" \
  -H "Accept-Language: en" \
  -d '{"message":"Tell me about your services"}'
```

### 2. Với JavaScript/Fetch

```javascript
// Guest user
const response = await fetch('http://localhost:8000/api/v1/chatbot', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept-Language': 'vi'
  },
  body: JSON.stringify({
    message: 'Tôi muốn biết giờ làm việc'
  })
});

const data = await response.json();
console.log(data.data.message);
```

```javascript
// Authenticated user
const response = await fetch('http://localhost:8000/api/v1/chatbot', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept-Language': 'vi',
    'Authorization': 'Bearer ' + token
  },
  body: JSON.stringify({
    message: 'Chi nhánh nào gần tôi nhất?'
  })
});
```

### 3. Với Axios

```javascript
import axios from 'axios';

const chatWithBot = async (message, locale = 'vi') => {
  try {
    const response = await axios.post('/api/v1/chatbot', 
      { message },
      {
        headers: {
          'Accept-Language': locale
        }
      }
    );
    return response.data.data.message;
  } catch (error) {
    console.error('Chatbot error:', error);
    throw error;
  }
};

// Sử dụng
const reply = await chatWithBot('Dịch vụ nào phổ biến nhất?');
console.log(reply);
```

## Giới hạn và Xử lý

### Chatbot CHẤP NHẬN trả lời:
- Thông tin về doanh nghiệp
- Danh sách và chi tiết chi nhánh
- Danh sách và chi tiết dịch vụ
- Giờ làm việc
- Thông tin liên hệ

### Chatbot TỪ CHỐI trả lời:
- Chẩn đoán bệnh
- Tư vấn điều trị cụ thể
- Thông tin về đối thủ
- Chính trị, cổ phiếu, vấn đề nhạy cảm khác
- Câu hỏi ngoài phạm vi kinh doanh

### Error Handling

Service tự động xử lý các lỗi:
- **API Key missing**: Báo lỗi cấu hình
- **Connection timeout**: Báo lỗi kết nối
- **API error**: Log chi tiết và trả lỗi chung cho user
- **Empty response**: Báo lỗi không nhận được phản hồi

## Tùy chỉnh

### 1. Thay đổi thông tin doanh nghiệp

Chỉnh sửa method `getBusinessInfo()` trong `ChatbotRepository.php`:

```php
public function getBusinessInfo(): array
{
    return [
        'name' => [...],
        'description' => [...],
        // Thêm thông tin khác
    ];
}
```

### 2. Thay đổi System Instruction

Chỉnh sửa method `getSystemInstruction()` trong `ChatbotService.php` để thay đổi cách chatbot phản hồi.

### 3. Thay đổi Temperature (Độ sáng tạo)

Trong `ChatbotService.php`, method `chat()`:
```php
'generationConfig' => [
    'temperature' => 0.7,  // 0.0 - 1.0 (0 = chính xác, 1 = sáng tạo)
    // ...
],
```

### 4. Thay đổi giới hạn độ dài

Trong `ChatRequest.php`:
```php
'message' => 'required|string|min:1|max:1000',  // Thay đổi max
```

## Testing

### Test với Postman

Import collection từ `docs/` hoặc tạo request mới:
1. Method: POST
2. URL: `http://localhost:8000/api/v1/chatbot`
3. Headers:
   - `Content-Type: application/json`
   - `Accept-Language: vi`
4. Body (raw JSON):
   ```json
   {
     "message": "Test message"
   }
   ```

### Các câu hỏi test mẫu

**Tiếng Việt:**
- "Cho tôi biết thông tin về phòng khám"
- "Các chi nhánh ở đâu?"
- "Dịch vụ chăm sóc da giá bao nhiêu?"
- "Giờ làm việc là mấy giờ?"

**English:**
- "Tell me about your clinic"
- "Where are your branches?"
- "How much does skin care service cost?"
- "What are your working hours?"

## Troubleshooting

### Lỗi: "Gemini API key is not configured"
**Giải pháp**: Kiểm tra file `.env` đã có `GEMINI_API_KEY`

### Lỗi: "Connection error with AI service"
**Giải pháp**: 
- Kiểm tra kết nối internet
- Verify API key còn hạn sử dụng

### Lỗi: "No response received from AI service"
**Giải pháp**: Gemini có thể đã block response do safety settings. Kiểm tra logs.

### Response không đúng ngôn ngữ
**Giải pháp**: Kiểm tra header `Accept-Language` trong request

## Performance

- **Response time**: ~2-5 giây (tùy thuộc Gemini API)
- **Rate limiting**: Áp dụng throttle theo API routes
- **Timeout**: 30 giây

## Security

- ✅ Input validation
- ✅ Safety settings (block harmful content)
- ✅ Scope limitation (chỉ trả lời thông tin kinh doanh)
- ✅ Error masking (không expose sensitive info)
- ✅ API key bảo mật trong .env

## Tích hợp Frontend

### React Example

```jsx
import { useState } from 'react';

function ChatBot() {
  const [message, setMessage] = useState('');
  const [response, setResponse] = useState('');
  const [loading, setLoading] = useState(false);

  const sendMessage = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/v1/chatbot', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept-Language': 'vi'
        },
        body: JSON.stringify({ message })
      });
      const data = await res.json();
      setResponse(data.data.message);
    } catch (error) {
      console.error(error);
      setResponse('Đã có lỗi xảy ra');
    }
    setLoading(false);
  };

  return (
    <div>
      <input 
        value={message}
        onChange={(e) => setMessage(e.target.value)}
        placeholder="Nhập câu hỏi..."
      />
      <button onClick={sendMessage} disabled={loading}>
        {loading ? 'Đang xử lý...' : 'Gửi'}
      </button>
      {response && <div className="response">{response}</div>}
    </div>
  );
}
```

## License

Sử dụng nội bộ - Beauty Clinic Project
