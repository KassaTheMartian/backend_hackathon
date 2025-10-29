# Chatbot API Quick Reference

## Endpoint
```
POST /api/v1/chatbot
```

## Headers
```http
Content-Type: application/json
Accept-Language: vi|en
X-Chat-Session: <uuid>  # Optional: for guest session persistence
```

## Request Body

### Authenticated User
```json
{
  "message": "Xin chào, tôi muốn tư vấn về dịch vụ"
}
```

### Guest User (recommended)
```json
{
  "message": "Chi nhánh của bạn ở đâu?",
  "session_key": "a7b3c9e2-4f5d-8c3a-1b2e-9d8c7e6f5a4b"
}
```

## Response
```json
{
  "success": true,
  "message": "Tạo phản hồi thành công",
  "data": {
    "message": "Chúng tôi có 3 chi nhánh tại Quận 1, Quận 3 và Quận 7...",
    "user_id": null,
    "locale": "vi",
    "session_key": "a7b3c9e2-4f5d-8c3a-1b2e-9d8c7e6f5a4b"
  }
}
```

## Features
- ✅ Remembers last 5 message pairs (10 messages total)
- ✅ Guest sessions via `session_key` (store in localStorage)
- ✅ Authenticated sessions linked to `user_id`
- ✅ Context-aware responses using conversation history
- ✅ Multi-language support (vi, en)

## Frontend Setup (JavaScript)

```javascript
// Generate or load session key
let sessionKey = localStorage.getItem('chat_session_key') || crypto.randomUUID();
localStorage.setItem('chat_session_key', sessionKey);

// Send message
const response = await fetch('/api/v1/chatbot', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept-Language': 'vi',
    'X-Chat-Session': sessionKey
  },
  body: JSON.stringify({ message: 'Xin chào' })
});

const data = await response.json();

// Update session key if server created a new one
if (data.data.session_key !== sessionKey) {
  localStorage.setItem('chat_session_key', data.data.session_key);
}
```

## Database Tables

### chat_sessions
- `id`: Primary key
- `user_id`: For authenticated users (nullable)
- `session_key`: UUID for guest users (nullable, unique)
- `meta`: JSON metadata (optional)
- `last_activity`: Timestamp of last message
- `is_active`: Boolean flag

### chat_messages
- `id`: Primary key
- `chat_session_id`: Foreign key to chat_sessions
- `user_id`: User who sent message (nullable)
- `role`: 'user' or 'assistant'
- `message`: Text content
- `meta`: JSON metadata (optional)
- `created_at`, `updated_at`: Timestamps

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Tin nhắn là bắt buộc",
  "errors": {
    "message": ["Tin nhắn là bắt buộc"]
  }
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Lỗi khi giao tiếp với dịch vụ AI",
  "error": {
    "type": "Tạo phản hồi thất bại",
    "code": "CHATBOT_ERROR"
  }
}
```

## Documentation
- Full integration guide: `docs/CHATBOT_SESSION_MEMORY.md`
- Frontend examples: `docs/CHATBOT_FRONTEND_INTEGRATION.md`
- Multi-language setup: `docs/CHATBOT_MULTILANGUAGE.md`
