# Hướng Dẫn Sử Dụng Mock API cho Frontend

Tài liệu này hướng dẫn cách sử dụng Mock API để phát triển Frontend trước khi Backend hoàn thiện.

## 📋 Mục Lục
1. [Giới Thiệu](#giới-thiệu)
2. [Cài Đặt Postman](#cài-đặt-postman)
3. [Sử Dụng Mockoon](#sử-dụng-mockoon)
4. [JSON Server](#json-server)
5. [Tích Hợp vào Frontend](#tích-hợp-vào-frontend)
6. [Các Endpoint Chính](#các-endpoint-chính)

---

## Giới Thiệu

Mock API giúp Frontend team có thể:
- ✅ Phát triển giao diện ngay lập tức
- ✅ Test các trường hợp khác nhau
- ✅ Không cần chờ Backend hoàn thiện
- ✅ Work độc lập với Backend team

## Cài Đặt Postman

### Bước 1: Import Collection
1. Mở Postman
2. Click **Import** ở góc trên bên trái
3. Chọn file `Beauty_Clinic_API.postman_collection.json`
4. Collection sẽ được thêm vào workspace

### Bước 2: Tạo Mock Server
1. Right-click vào collection **Beauty Clinic API - Mock Responses**
2. Chọn **Add Mock Server**
3. Chọn **Create Mock Server**
4. Copy **Mock Server URL** (ví dụ: `https://abc-123.mockapi.io`)
5. Click **Create Mock Server**

### Bước 3: Sử Dụng
- Thay `{{base_url}}` trong collection bằng Mock Server URL
- Bắt đầu call API từ Frontend!

**Lưu ý:** Postman Mock Server có giới hạn request miễn phí. Cân nhắc upgrade hoặc dùng giải pháp khác cho production.

---

## Sử Dụng Mockoon

Mockoon là tool miễn phí, không giới hạn requests.

### Cài Đặt
```bash
# macOS
brew install mockoon-cli

# Windows
# Download từ: https://mockoon.com/download/

# npm
npm install -g @mockoon/cli
```

### Import Collection
1. Mở Mockoon Desktop App
2. File > Import > OpenAPI/Swagger
3. Hoặc tạo routes thủ công dựa trên `MOCK_API_RESPONSES.md`

### Chạy Mock Server
```bash
mockoon-cli start -d mock-api.json -p 3001
```

Server sẽ chạy tại: `http://localhost:3001`

---

## JSON Server

JSON Server là cách đơn giản nhất để tạo mock API.

### Cài Đặt
```bash
npm install -g json-server
```

### Tạo file `db.json`
```json
{
  "services": [
    {
      "id": 1,
      "name": "Điều trị mụn chuyên sâu",
      "price": 500000,
      "category_id": 1
    }
  ],
  "branches": [
    {
      "id": 1,
      "name": "Chi nhánh Quận 1",
      "address": "123 Nguyễn Huệ"
    }
  ],
  "users": [],
  "bookings": []
}
```

### Chạy JSON Server
```bash
json-server --watch db.json --port 3001
```

### Sử Dụng
```
GET  http://localhost:3001/services
POST http://localhost:3001/services
GET  http://localhost:3001/bookings
```

**Nhược điểm:** Không support các response format phức tạp như trong API thực.

---

## Tích Hợp vào Frontend

### 1. Cài Đặt Axios
```bash
npm install axios
```

### 2. Tạo API Client
```javascript
// src/services/api.js
import axios from 'axios';

const API_BASE_URL = 'https://api.beautyclinic.com/api/v1';
// Hoặc dùng mock: http://localhost:3001

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### 3. Tạo Service Functions
```javascript
// src/services/authService.js
import api from './api';

export const authService = {
  login: async (email, password) => {
    const response = await api.post('/auth/login', {
      email,
      password,
      device_name: 'Web App'
    });
    return response.data;
  },
  
  register: async (userData) => {
    const response = await api.post('/auth/register', userData);
    return response.data;
  },
  
  logout: async () => {
    const response = await api.post('/auth/logout');
    return response.data;
  },
};
```

### 4. Sử Dụng trong Component
```javascript
// src/components/LoginForm.js
import { authService } from '../services/authService';

const LoginForm = () => {
  const handleLogin = async (email, password) => {
    try {
      const response = await authService.login(email, password);
      // Save token
      localStorage.setItem('token', response.data.token);
      console.log('Login successful:', response.data);
    } catch (error) {
      console.error('Login failed:', error);
    }
  };
  
  return (
    // Form UI
  );
};
```

---

## Các Endpoint Chính

### Authentication
```
POST   /api/v1/auth/login
POST   /api/v1/auth/register
POST   /api/v1/auth/logout
GET    /api/v1/auth/me
```

### Services
```
GET    /api/v1/services
GET    /api/v1/services/:id
GET    /api/v1/service-categories
```

### Branches
```
GET    /api/v1/branches
GET    /api/v1/branches/:id
GET    /api/v1/branches/:id/available-slots
```

### Bookings
```
GET    /api/v1/bookings
POST   /api/v1/bookings
GET    /api/v1/bookings/:id
PUT    /api/v1/bookings/:id
POST   /api/v1/bookings/:id/cancel
GET    /api/v1/my-bookings
```

### Payments
```
POST   /api/v1/payments/create-intent
POST   /api/v1/payments/confirm
```

### Reviews
```
GET    /api/v1/reviews
POST   /api/v1/reviews
GET    /api/v1/reviews/:id
```

### Blog
```
GET    /api/v1/posts
GET    /api/v1/posts/:slug
GET    /api/v1/posts/featured
```

### Contact
```
POST   /api/v1/contact
```

### Profile
```
GET    /api/v1/profile
PUT    /api/v1/profile
GET    /api/v1/profile/promotions
```

---

## Cấu Trúc Response

Tất cả responses đều follow format:

```json
{
  "success": true|false,
  "message": "Message string",
  "data": { /* Actual data */ },
  "error": null | {
    "type": "ErrorType",
    "code": "ERROR_CODE",
    "details": {}
  },
  "meta": {
    "page": 1,
    "page_size": 15,
    "total_count": 100,
    "total_pages": 7,
    "has_next_page": true,
    "has_previous_page": false
  },
  "trace_id": "unique-trace-id",
  "timestamp": "2025-10-27T10:00:00Z"
}
```

---

## Testing Tips

### 1. Test các trường hợp khác nhau
- Success responses
- Error responses (400, 401, 404, 500)
- Empty data
- Pagination

### 2. Sử dụng Postman/Insomnia
- Test API trước khi code Frontend
- Understand request/response format
- Save requests làm reference

### 3. Mock Data Variations
- Thay đổi data trong mock responses
- Test edge cases
- Test với data lớn (pagination)

### 4. Error Handling
```javascript
try {
  const response = await api.post('/auth/login', data);
  // Success handling
} catch (error) {
  if (error.response) {
    // Server responded with error
    console.error('Error:', error.response.data);
  } else if (error.request) {
    // Request made but no response
    console.error('Network error');
  } else {
    // Something else happened
    console.error('Error:', error.message);
  }
}
```

---

## Environment Variables

Sử dụng environment variables để switch giữa mock và real API:

```javascript
// .env.development
REACT_APP_API_BASE_URL=http://localhost:3001

// .env.production  
REACT_APP_API_BASE_URL=https://api.beautyclinic.com/api/v1
```

```javascript
// api.js
const API_BASE_URL = process.env.REACT_APP_API_BASE_URL;
```

---

## Debugging

### Console Logging
```javascript
api.interceptors.response.use(
  (response) => {
    console.log('API Response:', response.data);
    return response;
  },
  (error) => {
    console.error('API Error:', error.response?.data);
    return Promise.reject(error);
  }
);
```

### Network Tab
- Check request/response headers
- Verify payload
- Check status codes

### Postman Console
- Monitor all API calls
- Debug request data
- Test edge cases

---

## Migration từ Mock sang Real API

Khi Backend sẵn sàng:

1. Update API_BASE_URL trong environment
2. Test tất cả endpoints
3. Verify response format giống nhau
4. Handle các edge cases thực tế
5. Test authentication flow
6. Test error handling

---

## Notes

- Token được lưu trong localStorage: `localStorage.setItem('token', token)`
- Sử dụng relative paths trong axios: `/api/v1/auth/login`
- Always handle loading và error states
- Implement proper caching khi cần
- Consider using React Query or SWR cho data fetching

---

## Resources

- 📄 [API Documentation](./API_DOCUMENTATION.md) - Full API docs
- 📄 [Mock Responses](./MOCK_API_RESPONSES.md) - All mock responses
- 📦 [Postman Collection](./Beauty_Clinic_API.postman_collection.json) - Postman import file
- 🏗️ [Architecture](./ARCHITECTURE_DIAGRAMS.md) - System architecture

---

**Happy Coding! 🚀**

