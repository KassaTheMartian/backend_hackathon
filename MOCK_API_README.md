# 🚀 Beauty Clinic Mock API - Chuẩn cho Frontend

Tổng hợp Mock API và tài liệu để Frontend team có thể tích hợp và phát triển ngay lập tức.

---

## 📁 Files Đã Tạo

### 1. **MOCK_API_RESPONSES.md**
📋 Chứa tất cả mock responses cho mọi endpoint
- Request/Response examples
- Error responses
- Pagination examples
- Sample data đầy đủ

### 2. **Beauty_Clinic_API.postman_collection.json**
📦 Postman collection có thể import
- Tất cả endpoints đã setup sẵn
- Có thể tạo mock server ngay
- Ready to use

### 3. **MOCK_API_SETUP_GUIDE.md**
📖 Hướng dẫn chi tiết sử dụng
- Setup với Postman
- Setup với Mockoon
- Setup với JSON Server
- Code examples cho Frontend
- Environment variables
- Debugging tips

---

## 🎯 Quick Start

### Option 1: Postman (Khuyên dùng)
1. Import file `Beauty_Clinic_API.postman_collection.json` vào Postman
2. Tạo Mock Server
3. Update `{{base_url}}` với Mock Server URL
4. Bắt đầu test!

### Option 2: Mockoon
1. Download Mockoon: https://mockoon.com
2. Import collection hoặc tạo routes thủ công
3. Start server tại `localhost:3001`
4. Call API!

### Option 3: JSON Server
1. Cài đặt: `npm install -g json-server`
2. Tạo file `db.json` với sample data
3. Chạy: `json-server --watch db.json`
4. Call API tại `http://localhost:3000`

---

## 📚 API Endpoints

### Authentication
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

### Services
- `GET /api/v1/services` (List)
- `GET /api/v1/services/:id` (Detail)
- `GET /api/v1/service-categories`

### Branches
- `GET /api/v1/branches` (List)
- `GET /api/v1/branches/:id` (Detail)
- `GET /api/v1/branches/:id/available-slots`

### Bookings
- `GET /api/v1/bookings` (Admin)
- `POST /api/v1/bookings` (Create)
- `GET /api/v1/my-bookings` (User)
- `GET /api/v1/bookings/:id`
- `PUT /api/v1/bookings/:id`
- `POST /api/v1/bookings/:id/cancel`

### Payments
- `POST /api/v1/payments/create-intent`
- `POST /api/v1/payments/confirm`

### Reviews
- `GET /api/v1/reviews`
- `POST /api/v1/reviews`

### Blog
- `GET /api/v1/posts`
- `GET /api/v1/posts/:slug`
- `GET /api/v1/posts/featured`

### Contact
- `POST /api/v1/contact`

### Chatbot
- `POST /api/v1/chatbot/message`
- `GET /api/v1/chatbot/sessions`

### Profile
- `GET /api/v1/profile`
- `PUT /api/v1/profile`
- `GET /api/v1/profile/promotions`

---

## 📝 Response Format

### Success
```json
{
  "success": true,
  "message": "OK",
  "data": { /* actual data */ },
  "error": null,
  "meta": {
    "page": 1,
    "page_size": 15,
    "total_count": 100,
    "total_pages": 7,
    "has_next_page": true,
    "has_previous_page": false
  },
  "trace_id": "trace-123",
  "timestamp": "2025-10-27T10:00:00Z"
}
```

### Error
```json
{
  "success": false,
  "message": "Error message",
  "data": null,
  "error": {
    "type": "ErrorType",
    "code": "ERROR_CODE",
    "details": {}
  },
  "meta": null,
  "trace_id": "trace-123",
  "timestamp": "2025-10-27T10:00:00Z"
}
```

---

## 🔑 Authentication

### Login Flow
```javascript
POST /api/v1/auth/login
Body: { email, password }
Response: { token, user }
```

### Using Token
```javascript
Headers: {
  "Authorization": "Bearer {token}"
}
```

### Token Storage
```javascript
// Save
localStorage.setItem('token', response.data.token);

// Use
const token = localStorage.getItem('token');
```

---

## 🎨 Frontend Integration

### Setup API Client
```javascript
// src/services/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.REACT_APP_API_BASE_URL || 'http://localhost:3001',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add auth token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### Example Usage
```javascript
// src/services/authService.js
import api from './api';

export const authService = {
  login: async (email, password) => {
    const response = await api.post('/auth/login', {
      email,
      password,
    });
    return response.data;
  },
};
```

---

## 📖 Xem Chi Tiết

- **[Hướng Dẫn Setup](./MOCK_API_SETUP_GUIDE.md)** - Setup chi tiết từng tool
- **[Mock Responses](./MOCK_API_RESPONSES.md)** - Tất cả mock data
- **[API Docs](./API_DOCUMENTATION.md)** - API documentation đầy đủ

---

## 💡 Tips

1. **Start Simple**: Test với Postman trước
2. **Use Environment Variables**: Switch giữa mock và real API dễ dàng
3. **Handle Errors**: Luôn implement error handling
4. **Test Edge Cases**: Empty data, pagination, errors
5. **Cache Data**: Sử dụng React Query hoặc SWR

---

## 🚨 Important Notes

- All timestamps are in ISO 8601 format
- Token expires sau 1 hour (có thể config)
- Pagination default: `page=1, per_page=15`
- Rate limiting: 60 requests/minute
- All endpoints support `locale` query param (vi, en, ja, zh)

---

## 📞 Support

Nếu có vấn đề với mock API:
1. Check [MOCK_API_SETUP_GUIDE.md](./MOCK_API_SETUP_GUIDE.md)
2. Verify API base URL
3. Check authentication token
4. Review request/response format

---

**Ready to build! 🎉**

