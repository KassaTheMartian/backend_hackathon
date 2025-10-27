# Mock API Responses - Beauty Clinic System

Tài liệu này cung cấp các mock responses chuẩn cho tất cả API endpoints, giúp Frontend có thể tích hợp và test trước khi backend hoàn thiện.

## Cấu trúc Response Chuẩn

### Response Thành công
```json
{
  "success": true,
  "message": "OK",
  "data": { /* data */ },
  "error": null,
  "meta": null,
  "trace_id": "mock-trace-123",
  "timestamp": "2025-10-27T10:00:00Z"
}
```

### Response Lỗi
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
  "trace_id": "mock-trace-123",
  "timestamp": "2025-10-27T10:00:00Z"
}
```

---

## 1. Authentication API

### POST /api/v1/auth/login

**Request:**
```json
{
  "email": "user@example.com",
  "password": "Password123!",
  "device_name": "Chrome Browser"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Nguyễn Văn A",
      "email": "user@example.com",
      "phone": "+84901234567",
      "avatar": "https://via.placeholder.com/150",
      "language_preference": "vi"
    },
    "token": "1|mockToken123456789abcdefghijklmnopqrstuvwxyz",
    "token_type": "Bearer"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-login-001",
  "timestamp": "2025-10-27T10:00:00Z"
}
```

### POST /api/v1/auth/register

**Request:**
```json
{
  "name": "Trần Thị B",
  "email": "newuser@example.com",
  "phone": "+84987654321",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "language_preference": "vi"
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "Registration successful. Please verify your email.",
  "data": {
    "user": {
      "id": 2,
      "name": "Trần Thị B",
      "email": "newuser@example.com",
      "phone": "+84987654321",
      "language_preference": "vi",
      "email_verified_at": null
    },
    "token": "2|newUserToken987654321abcdefghijklmnopqrstuv"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-register-001",
  "timestamp": "2025-10-27T10:05:00Z"
}
```

### POST /api/v1/auth/logout

**Response 200:**
```json
{
  "success": true,
  "message": "Logged out successfully",
  "data": null,
  "error": null,
  "meta": null,
  "trace_id": "trace-logout-001",
  "timestamp": "2025-10-27T10:10:00Z"
}
```

### GET /api/v1/auth/me

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "name": "Nguyễn Văn A",
    "email": "user@example.com",
    "phone": "+84901234567",
    "avatar": "https://via.placeholder.com/150",
    "language_preference": "vi",
    "created_at": "2024-01-15T00:00:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-me-001",
  "timestamp": "2025-10-27T10:15:00Z"
}
```

---

## 2. Services API

### GET /api/v1/services

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "category": {
        "id": 1,
        "name": "Chăm sóc da mặt",
        "slug": "cham-soc-da-mat"
      },
      "name": "Điều trị mụn chuyên sâu",
      "slug": "dieu-tri-mun-chuyen-sau",
      "description": "Liệu trình điều trị mụn chuyên sâu với công nghệ hiện đại",
      "short_description": "Giảm mụn hiệu quả với liệu trình chuyên sâu",
      "price": 500000,
      "discounted_price": 450000,
      "duration": 60,
      "image": "https://via.placeholder.com/400x300?text=Acne+Treatment",
      "rating": 4.5,
      "total_reviews": 120,
      "is_featured": true,
      "created_at": "2025-01-10T00:00:00Z"
    },
    {
      "id": 2,
      "category": {
        "id": 1,
        "name": "Chăm sóc da mặt",
        "slug": "cham-soc-da-mat"
      },
      "name": "Chăm sóc da bằng tảo biển",
      "slug": "cham-soc-da-bang-tao-bien",
      "description": "Dưỡng ẩm và làm sáng da với tảo biển tự nhiên",
      "short_description": "Làn da mềm mại với tảo biển",
      "price": 600000,
      "discounted_price": null,
      "duration": 90,
      "image": "https://via.placeholder.com/400x300?text=Seaweed+Treatment",
      "rating": 4.8,
      "total_reviews": 95,
      "is_featured": true,
      "created_at": "2025-01-12T00:00:00Z"
    },
    {
      "id": 3,
      "category": {
        "id": 2,
        "name": "Trắng da",
        "slug": "trang-da"
      },
      "name": "Trắng da công nghệ cao",
      "slug": "trang-da-cong-nghe-cao",
      "description": "Làm trắng da an toàn với công nghệ hiện đại",
      "short_description": "Da trắng sáng tự nhiên",
      "price": 800000,
      "discounted_price": 720000,
      "duration": 90,
      "image": "https://via.placeholder.com/400x300?text=Whitening+Treatment",
      "rating": 4.6,
      "total_reviews": 200,
      "is_featured": false,
      "created_at": "2025-01-15T00:00:00Z"
    }
  ],
  "error": null,
  "meta": {
    "page": 1,
    "page_size": 15,
    "total_count": 50,
    "total_pages": 4,
    "has_next_page": true,
    "has_previous_page": false
  },
  "trace_id": "trace-services-list",
  "timestamp": "2025-10-27T10:20:00Z"
}
```

### GET /api/v1/services/1

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "category": {
      "id": 1,
      "name": "Chăm sóc da mặt",
      "slug": "cham-soc-da-mat",
      "description": "Các dịch vụ chăm sóc da mặt chuyên nghiệp"
    },
    "name": "Điều trị mụn chuyên sâu",
    "slug": "dieu-tri-mun-chuyen-sau",
    "description": "<p>Liệu trình điều trị mụn chuyên sâu với các bước:</p><ul><li>Rửa mặt sạch sâu</li><li>Tẩy tế bào chết</li><li>Hút mụn chuyên nghiệp</li><li>Đắp mặt nạ dưỡng da</li><li>Dưỡng ẩm và bảo vệ da</li></ul>",
    "short_description": "Giảm mụn hiệu quả với liệu trình chuyên sâu",
    "price": 500000,
    "discounted_price": 450000,
    "duration": 60,
    "image": "https://via.placeholder.com/800x600?text=Acne+Treatment+Detail",
    "gallery": [
      "https://via.placeholder.com/800x600?text=Gallery+1",
      "https://via.placeholder.com/800x600?text=Gallery+2",
      "https://via.placeholder.com/800x600?text=Gallery+3"
    ],
    "rating": 4.5,
    "total_reviews": 120,
    "is_featured": true,
    "available_branches": [
      {
        "id": 1,
        "name": "Chi nhánh Quận 1",
        "address": "123 Nguyễn Huệ, Quận 1, TP.HCM"
      },
      {
        "id": 2,
        "name": "Chi nhánh Quận 3",
        "address": "456 Võ Văn Tần, Quận 3, TP.HCM"
      }
    ],
    "related_services": [
      {
        "id": 2,
        "name": "Chăm sóc da bằng tảo biển",
        "slug": "cham-soc-da-bang-tao-bien",
        "price": 600000,
        "image": "https://via.placeholder.com/400x300?text=Related+1"
      },
      {
        "id": 4,
        "name": "Thu nhỏ lỗ chân lông",
        "slug": "thu-nho-lo-chan-long",
        "price": 400000,
        "image": "https://via.placeholder.com/400x300?text=Related+2"
      }
    ],
    "created_at": "2025-01-10T00:00:00Z",
    "updated_at": "2025-10-15T00:00:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-service-detail",
  "timestamp": "2025-10-27T10:25:00Z"
}
```

### GET /api/v1/service-categories

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "name": "Chăm sóc da mặt",
      "slug": "cham-soc-da-mat",
      "description": "Các dịch vụ chăm sóc da mặt chuyên nghiệp",
      "icon": "fas fa-smile",
      "services_count": 15,
      "image": "https://via.placeholder.com/300x200?text=Category+1"
    },
    {
      "id": 2,
      "name": "Trắng da",
      "slug": "trang-da",
      "description": "Làm trắng da an toàn hiệu quả",
      "icon": "fas fa-star",
      "services_count": 10,
      "image": "https://via.placeholder.com/300x200?text=Category+2"
    },
    {
      "id": 3,
      "name": "Giảm béo",
      "slug": "giam-beo",
      "description": "Giảm mỡ thừa, săn chắc cơ thể",
      "icon": "fas fa-heart",
      "services_count": 8,
      "image": "https://via.placeholder.com/300x200?text=Category+3"
    },
    {
      "id": 4,
      "name": "Massage thư giãn",
      "slug": "massage-thu-gian",
      "description": "Thư giãn và giảm stress",
      "icon": "fas fa-hand-sparkles",
      "services_count": 12,
      "image": "https://via.placeholder.com/300x200?text=Category+4"
    }
  ],
  "error": null,
  "meta": null,
  "trace_id": "trace-categories",
  "timestamp": "2025-10-27T10:30:00Z"
}
```

---

## 3. Branches API

### GET /api/v1/branches

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "name": "Chi nhánh Quận 1",
      "slug": "chi-nhanh-quan-1",
      "address": "123 Nguyễn Huệ, Quận 1, TP.HCM",
      "phone": "+84281234567",
      "email": "quan1@beautyclinic.com",
      "latitude": 10.7769,
      "longitude": 106.7009,
      "distance": 2.5,
      "opening_hours": {
        "monday": {
          "open": "09:00",
          "close": "18:00",
          "is_closed": false
        },
        "tuesday": {
          "open": "09:00",
          "close": "18:00",
          "is_closed": false
        },
        "wednesday": {
          "open": "09:00",
          "close": "18:00",
          "is_closed": false
        },
        "thursday": {
          "open": "09:00",
          "close": "18:00",
          "is_closed": false
        },
        "friday": {
          "open": "09:00",
          "close": "18:00",
          "is_closed": false
        },
        "saturday": {
          "open": "09:00",
          "close": "17:00",
          "is_closed": false
        },
        "sunday": {
          "open": "09:00",
          "close": "13:00",
          "is_closed": false
        }
      },
      "images": [
        "https://via.placeholder.com/800x600?text=Branch+1",
        "https://via.placeholder.com/800x600?text=Branch+Interior"
      ],
      "amenities": ["wifi", "parking", "wheelchair_access", "air_conditioning"],
      "rating": 4.7,
      "total_reviews": 350
    },
    {
      "id": 2,
      "name": "Chi nhánh Quận 3",
      "slug": "chi-nhanh-quan-3",
      "address": "456 Võ Văn Tần, Quận 3, TP.HCM",
      "phone": "+84281234568",
      "email": "quan3@beautyclinic.com",
      "latitude": 10.7834,
      "longitude": 106.6916,
      "distance": 5.2,
      "opening_hours": {
        "monday": {
          "open": "08:00",
          "close": "20:00",
          "is_closed": false
        },
        "tuesday": {
          "open": "08:00",
          "close": "20:00",
          "is_closed": false
        },
        "wednesday": {
          "open": "08:00",
          "close": "20:00",
          "is_closed": false
        },
        "thursday": {
          "open": "08:00",
          "close": "20:00",
          "is_closed": false
        },
        "friday": {
          "open": "08:00",
          "close": "20:00",
          "is_closed": false
        },
        "saturday": {
          "open": "08:00",
          "close": "18:00",
          "is_closed": false
        },
        "sunday": {
          "open": "08:00",
          "close": "17:00",
          "is_closed": false
        }
      },
      "images": [
        "https://via.placeholder.com/800x600?text=Branch+2"
      ],
      "amenities": ["wifi", "parking", "wheelchair_access"],
      "rating": 4.5,
      "total_reviews": 280
    }
  ],
  "error": null,
  "meta": null,
  "trace_id": "trace-branches-list",
  "timestamp": "2025-10-27T10:35:00Z"
}
```

### GET /api/v1/branches/1

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "name": "Chi nhánh Quận 1",
    "slug": "chi-nhanh-quan-1",
    "address": "123 Nguyễn Huệ, Quận 1, TP.HCM",
    "phone": "+84281234567",
    "email": "quan1@beautyclinic.com",
    "latitude": 10.7769,
    "longitude": 106.7009,
    "opening_hours": {
      "monday": {
        "open": "09:00",
        "close": "18:00",
        "is_closed": false
      },
      "tuesday": {
        "open": "09:00",
        "close": "18:00",
        "is_closed": false
      },
      "wednesday": {
        "open": "09:00",
        "close": "18:00",
        "is_closed": false
      },
      "thursday": {
        "open": "09:00",
        "close": "18:00",
        "is_closed": false
      },
      "friday": {
        "open": "09:00",
        "close": "18:00",
        "is_closed": false
      },
      "saturday": {
        "open": "09:00",
        "close": "17:00",
        "is_closed": false
      },
      "sunday": {
        "open": "09:00",
        "close": "13:00",
        "is_closed": false
      }
    },
    "description": "<p>Chi nhánh trung tâm với không gian rộng rãi, hiện đại, đầy đủ tiện nghi cho khách hàng.</p>",
    "images": [
      "https://via.placeholder.com/800x600?text=Branch+Main",
      "https://via.placeholder.com/800x600?text=Interior+1",
      "https://via.placeholder.com/800x600?text=Interior+2"
    ],
    "amenities": ["wifi", "parking", "wheelchair_access", "air_conditioning", "refreshments"],
    "rating": 4.7,
    "total_reviews": 350,
    "available_services": [
      {
        "id": 1,
        "name": "Điều trị mụn chuyên sâu",
        "slug": "dieu-tri-mun-chuyen-sau",
        "price": 450000,
        "image": "https://via.placeholder.com/400x300?text=Service"
      }
    ],
    "staff": [
      {
        "id": 1,
        "name": "Chị Nguyễn Thị A",
        "position": "Chuyên viên chăm sóc da",
        "avatar": "https://via.placeholder.com/150",
        "rating": 4.9,
        "total_reviews": 150
      },
      {
        "id": 2,
        "name": "Chị Trần Thị B",
        "position": "Chuyên viên trị liệu",
        "avatar": "https://via.placeholder.com/150",
        "rating": 4.8,
        "total_reviews": 120
      }
    ]
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-branch-detail",
  "timestamp": "2025-10-27T10:40:00Z"
}
```

### GET /api/v1/branches/1/available-slots?date=2025-10-28&service_id=1

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "date": "2025-10-28",
    "service_id": 1,
    "available_slots": [
      {
        "time": "09:00",
        "available": true,
        "staff": [
          {
            "id": 1,
            "name": "Chị Nguyễn Thị A",
            "avatar": "https://via.placeholder.com/100"
          },
          {
            "id": 2,
            "name": "Chị Trần Thị B",
            "avatar": "https://via.placeholder.com/100"
          }
        ]
      },
      {
        "time": "09:30",
        "available": true,
        "staff": [
          {
            "id": 1,
            "name": "Chị Nguyễn Thị A",
            "avatar": "https://via.placeholder.com/100"
          }
        ]
      },
      {
        "time": "10:00",
        "available": true,
        "staff": [
          {
            "id": 1,
            "name": "Chị Nguyễn Thị A",
            "avatar": "https://via.placeholder.com/100"
          },
          {
            "id": 2,
            "name": "Chị Trần Thị B",
            "avatar": "https://via.placeholder.com/100"
          }
        ]
      },
      {
        "time": "10:30",
        "available": false,
        "reason": "Fully booked",
        "staff": []
      },
      {
        "time": "11:00",
        "available": true,
        "staff": [
          {
            "id": 2,
            "name": "Chị Trần Thị B",
            "avatar": "https://via.placeholder.com/100"
          }
        ]
      },
      {
        "time": "14:00",
        "available": true,
        "staff": [
          {
            "id": 1,
            "name": "Chị Nguyễn Thị A",
            "avatar": "https://via.placeholder.com/100"
          },
          {
            "id": 2,
            "name": "Chị Trần Thị B",
            "avatar": "https://via.placeholder.com/100"
          }
        ]
      },
      {
        "time": "14:30",
        "available": true,
        "staff": [
          {
            "id": 2,
            "name": "Chị Trần Thị B",
            "avatar": "https://via.placeholder.com/100"
          }
        ]
      }
    ]
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-available-slots",
  "timestamp": "2025-10-27T10:45:00Z"
}
```

---

## 4. Bookings API

### POST /api/v1/bookings

**Request:**
```json
{
  "branch_id": 1,
  "service_id": 1,
  "staff_id": 1,
  "booking_date": "2025-10-28",
  "booking_time": "10:00",
  "notes": "Tôi muốn tư vấn thêm về sản phẩm sau điều trị",
  "promotion_code": "WELCOME10"
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "Booking created successfully. Confirmation email sent.",
  "data": {
    "id": 123,
    "booking_code": "BK20251028001",
    "branch": {
      "id": 1,
      "name": "Chi nhánh Quận 1",
      "address": "123 Nguyễn Huệ, Quận 1, TP.HCM",
      "phone": "+84281234567"
    },
    "service": {
      "id": 1,
      "name": "Điều trị mụn chuyên sâu",
      "slug": "dieu-tri-mun-chuyen-sau",
      "price": 500000,
      "duration": 60,
      "image": "https://via.placeholder.com/400x300?text=Service"
    },
    "staff": {
      "id": 1,
      "name": "Chị Nguyễn Thị A",
      "position": "Chuyên viên chăm sóc da"
    },
    "booking_date": "2025-10-28",
    "booking_time": "10:00",
    "duration": 60,
    "status": "pending",
    "payment_status": "pending",
    "service_price": 500000,
    "discount_amount": 50000,
    "total_amount": 450000,
    "notes": "Tôi muốn tư vấn thêm về sản phẩm sau điều trị",
    "created_at": "2025-10-27T15:00:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-booking-create",
  "timestamp": "2025-10-27T15:00:00Z"
}
```

### GET /api/v1/bookings/123

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 123,
    "booking_code": "BK20251028001",
    "customer": {
      "name": "Nguyễn Văn A",
      "email": "user@example.com",
      "phone": "+84901234567"
    },
    "branch": {
      "id": 1,
      "name": "Chi nhánh Quận 1",
      "address": "123 Nguyễn Huệ, Quận 1, TP.HCM",
      "phone": "+84281234567"
    },
    "service": {
      "id": 1,
      "name": "Điều trị mụn chuyên sâu",
      "slug": "dieu-tri-mun-chuyen-sau",
      "price": 500000,
      "duration": 60,
      "image": "https://via.placeholder.com/400x300?text=Service"
    },
    "staff": {
      "id": 1,
      "name": "Chị Nguyễn Thị A",
      "position": "Chuyên viên chăm sóc da",
      "avatar": "https://via.placeholder.com/150"
    },
    "booking_date": "2025-10-28",
    "booking_time": "10:00",
    "duration": 60,
    "status": "confirmed",
    "payment_status": "paid",
    "service_price": 500000,
    "discount_amount": 50000,
    "total_amount": 450000,
    "notes": "Tôi muốn tư vấn thêm về sản phẩm sau điều trị",
    "created_at": "2025-10-27T15:00:00Z",
    "confirmed_at": "2025-10-27T15:05:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-booking-detail",
  "timestamp": "2025-10-27T15:10:00Z"
}
```

### GET /api/v1/my-bookings

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 123,
      "booking_code": "BK20251028001",
      "service": {
        "id": 1,
        "name": "Điều trị mụn chuyên sâu",
        "image": "https://via.placeholder.com/400x300?text=Service"
      },
      "branch": {
        "id": 1,
        "name": "Chi nhánh Quận 1"
      },
      "booking_date": "2025-10-28",
      "booking_time": "10:00",
      "status": "confirmed",
      "payment_status": "paid",
      "total_amount": 450000,
      "created_at": "2025-10-27T15:00:00Z"
    },
    {
      "id": 122,
      "booking_code": "BK20251025002",
      "service": {
        "id": 2,
        "name": "Chăm sóc da bằng tảo biển",
        "image": "https://via.placeholder.com/400x300?text=Service"
      },
      "branch": {
        "id": 2,
        "name": "Chi nhánh Quận 3"
      },
      "booking_date": "2025-10-25",
      "booking_time": "14:00",
      "status": "completed",
      "payment_status": "paid",
      "total_amount": 600000,
      "created_at": "2025-10-24T10:00:00Z"
    },
    {
      "id": 121,
      "booking_code": "BK20251020003",
      "service": {
        "id": 3,
        "name": "Trắng da công nghệ cao",
        "image": "https://via.placeholder.com/400x300?text=Service"
      },
      "branch": {
        "id": 1,
        "name": "Chi nhánh Quận 1"
      },
      "booking_date": "2025-10-20",
      "booking_time": "09:00",
      "status": "cancelled",
      "payment_status": "refunded",
      "total_amount": 720000,
      "created_at": "2025-10-19T14:00:00Z",
      "cancelled_at": "2025-10-19T16:00:00Z"
    }
  ],
  "error": null,
  "meta": {
    "page": 1,
    "page_size": 15,
    "total_count": 10,
    "total_pages": 1,
    "has_next_page": false,
    "has_previous_page": false
  },
  "trace_id": "trace-my-bookings",
  "timestamp": "2025-10-27T15:15:00Z"
}
```

### POST /api/v1/bookings/123/cancel

**Request:**
```json
{
  "cancellation_reason": "Tôi có việc đột xuất"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Booking cancelled successfully",
  "data": {
    "id": 123,
    "booking_code": "BK20251028001",
    "status": "cancelled",
    "cancellation_reason": "Tôi có việc đột xuất",
    "cancelled_at": "2025-10-27T15:20:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-booking-cancel",
  "timestamp": "2025-10-27T15:20:00Z"
}
```

---

## 5. Payments API

### POST /api/v1/payments/create-intent

**Request:**
```json
{
  "booking_id": 123
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "client_secret": "pi_mock_secret_abcdefghijklmnopqrstuvwxyz123456",
    "payment_intent_id": "pi_mock_123456789",
    "amount": 450000,
    "currency": "vnd"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-payment-intent",
  "timestamp": "2025-10-27T15:25:00Z"
}
```

### POST /api/v1/payments/confirm

**Request:**
```json
{
  "booking_id": 123,
  "payment_intent_id": "pi_mock_123456789",
  "payment_method": "stripe"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Payment confirmed successfully",
  "data": {
    "payment_id": 456,
    "booking_id": 123,
    "amount": 450000,
    "status": "completed",
    "paid_at": "2025-10-27T15:30:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-payment-confirm",
  "timestamp": "2025-10-27T15:30:00Z"
}
```

---

## 6. Reviews API

### GET /api/v1/reviews

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "user": {
        "name": "Nguyễn Văn A",
        "avatar": "https://via.placeholder.com/100"
      },
      "service": {
        "id": 1,
        "name": "Điều trị mụn chuyên sâu"
      },
      "rating": 5,
      "title": "Dịch vụ tuyệt vời!",
      "comment": "Tôi rất hài lòng với dịch vụ điều trị mụn. Nhân viên chuyên nghiệp, thái độ thân thiện. Kết quả sau khi điều trị rất tốt.",
      "images": [
        "https://via.placeholder.com/400x400?text=Review+1"
      ],
      "helpful_count": 15,
      "admin_response": "Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi. Chúc bạn luôn tự tin với làn da khỏe đẹp!",
      "created_at": "2025-10-20T10:00:00Z"
    },
    {
      "id": 2,
      "user": {
        "name": "Trần Thị B",
        "avatar": "https://via.placeholder.com/100"
      },
      "service": {
        "id": 2,
        "name": "Chăm sóc da bằng tảo biển"
      },
      "rating": 4,
      "title": "Dịch vụ tốt",
      "comment": "Da mềm mịn hơn rất nhiều sau liệu trình. Sẽ quay lại.",
      "images": [],
      "helpful_count": 8,
      "admin_response": null,
      "created_at": "2025-10-18T14:30:00Z"
    }
  ],
  "error": null,
  "meta": {
    "page": 1,
    "page_size": 15,
    "total_count": 50,
    "total_pages": 4,
    "has_next_page": true,
    "has_previous_page": false
  },
  "trace_id": "trace-reviews-list",
  "timestamp": "2025-10-27T15:35:00Z"
}
```

### POST /api/v1/reviews

**Request:**
```json
{
  "booking_id": 123,
  "service_id": 1,
  "rating": 5,
  "title": "Dịch vụ tuyệt vời!",
  "comment": "Tôi rất hài lòng với dịch vụ",
  "service_quality_rating": 5,
  "staff_rating": 5,
  "cleanliness_rating": 5,
  "value_rating": 4,
  "images": []
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "Review submitted successfully. Waiting for approval.",
  "data": {
    "id": 51,
    "rating": 5,
    "is_approved": false
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-review-create",
  "timestamp": "2025-10-27T15:40:00Z"
}
```

---

## 7. Posts API (Blog)

### GET /api/v1/posts

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "title": "10 Bí quyết chăm sóc da mùa hè",
      "slug": "10-bi-quyet-cham-soc-da-mua-he",
      "excerpt": "Mùa hè đến, làn da cần được chăm sóc đặc biệt với ánh nắng mặt trời gay gắt. Hãy cùng khám phá những bí quyết giữ làn da khỏe đẹp trong mùa nóng.",
      "featured_image": "https://via.placeholder.com/800x400?text=Post+Featured",
      "category": {
        "id": 1,
        "name": "Chăm sóc da",
        "slug": "cham-soc-da"
      },
      "author": {
        "name": "Bác sĩ Nguyễn Văn A",
        "avatar": "https://via.placeholder.com/100"
      },
      "published_at": "2025-10-25T10:00:00Z",
      "reading_time": 5,
      "views_count": 1250,
      "tags": ["chăm sóc da", "mùa hè", "làm đẹp"]
    },
    {
      "id": 2,
      "title": "Xu hướng làm đẹp 2025",
      "slug": "xu-huong-lam-dep-2025",
      "excerpt": "Tổng quan về các xu hướng làm đẹp đang thịnh hành trong năm 2025, từ skincare đến makeup.",
      "featured_image": "https://via.placeholder.com/800x400?text=Post+2",
      "category": {
        "id": 2,
        "name": "Xu hướng làm đẹp",
        "slug": "xu-huong-lam-dep"
      },
      "author": {
        "name": "Chuyên gia Trần Thị B",
        "avatar": "https://via.placeholder.com/100"
      },
      "published_at": "2025-10-20T14:00:00Z",
      "reading_time": 7,
      "views_count": 980,
      "tags": ["xu hướng", "làm đẹp", "2025"]
    }
  ],
  "error": null,
  "meta": {
    "page": 1,
    "page_size": 15,
    "total_count": 25,
    "total_pages": 2,
    "has_next_page": true,
    "has_previous_page": false
  },
  "trace_id": "trace-posts-list",
  "timestamp": "2025-10-27T15:45:00Z"
}
```

### GET /api/v1/posts/10-bi-quyet-cham-soc-da-mua-he

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "title": "10 Bí quyết chăm sóc da mùa hè",
    "slug": "10-bi-quyet-cham-soc-da-mua-he",
    "content": "<h1>10 Bí quyết chăm sóc da mùa hè</h1><p>Mùa hè đến với ánh nắng gay gắt, khí hậu nóng ẩm. Làn da của bạn cần được chăm sóc đặc biệt để luôn khỏe mạnh và rạng rỡ.</p><h2>1. Thoa kem chống nắng mỗi ngày</h2><p>Kem chống nắng là bước đầu tiên và quan trọng nhất để bảo vệ da khỏi tác hại của tia UV.</p><h2>2. Tăng cường hydrat hóa</h2><p>Uống nhiều nước và sử dụng kem dưỡng ẩm để giữ da luôn mềm mại.</p>",
    "featured_image": "https://via.placeholder.com/800x400?text=Featured+Image",
    "images": [
      "https://via.placeholder.com/800x400?text=Image+1",
      "https://via.placeholder.com/800x400?text=Image+2"
    ],
    "category": {
      "id": 1,
      "name": "Chăm sóc da",
      "slug": "cham-soc-da"
    },
    "author": {
      "name": "Bác sĩ Nguyễn Văn A",
      "bio": "Chuyên gia 15 năm kinh nghiệm về chăm sóc da",
      "avatar": "https://via.placeholder.com/150"
    },
    "tags": ["chăm sóc da", "mùa hè", "làm đẹp"],
    "published_at": "2025-10-25T10:00:00Z",
    "reading_time": 5,
    "views_count": 1251,
    "related_posts": [
      {
        "id": 3,
        "title": "Làm thế nào để chọn kem chống nắng phù hợp?",
        "slug": "chon-kem-chong-nang-phu-hop",
        "featured_image": "https://via.placeholder.com/400x300?text=Related",
        "reading_time": 4
      }
    ],
    "meta": {
      "title": "10 Bí quyết chăm sóc da mùa hè - Beauty Clinic",
      "description": "Khám phá 10 bí quyết chăm sóc da hiệu quả trong mùa hè",
      "keywords": "chăm sóc da, mùa hè, làm đẹp"
    }
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-post-detail",
  "timestamp": "2025-10-27T15:50:00Z"
}
```

---

## 8. Contact API

### POST /api/v1/contact

**Request:**
```json
{
  "name": "Nguyễn Văn C",
  "email": "contact@example.com",
  "phone": "+84987654321",
  "subject": "Tư vấn dịch vụ",
  "message": "Tôi muốn được tư vấn về dịch vụ điều trị mụn"
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "Thank you for contacting us. We will respond soon.",
  "data": {
    "id": 100,
    "reference_code": "CT20251027001"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-contact-submit",
  "timestamp": "2025-10-27T16:00:00Z"
}
```

---

## 9. Chatbot API

### POST /api/v1/chatbot/message

**Request:**
```json
{
  "session_id": "sess_abc123xyz",
  "message": "Tôi muốn đặt lịch hẹn",
  "context": {
    "page": "booking",
    "service_id": 1
  }
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "session_id": "sess_abc123xyz",
    "bot_response": "Chào bạn! Tôi có thể giúp bạn đặt lịch hẹn. Bạn muốn đặt lịch cho dịch vụ nào?",
    "suggestions": [
      "Điều trị mụn",
      "Chăm sóc da",
      "Làm trắng da"
    ],
    "intent": "booking_inquiry",
    "confidence": 0.95
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-chatbot-message",
  "timestamp": "2025-10-27T16:05:00Z"
}
```

---

## 10. Profile API

### GET /api/v1/profile

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "name": "Nguyễn Văn A",
    "email": "user@example.com",
    "phone": "+84901234567",
    "avatar": "https://via.placeholder.com/150",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "address": "123 Đường ABC, Quận XYZ, TP.HCM",
    "language_preference": "vi",
    "total_bookings": 15,
    "total_spent": 10500000,
    "member_since": "2024-01-15T00:00:00Z",
    "email_verified_at": "2024-01-16T10:00:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-profile-get",
  "timestamp": "2025-10-27T16:10:00Z"
}
```

### PUT /api/v1/profile

**Request:**
```json
{
  "name": "Nguyễn Văn A",
  "phone": "+84901234567",
  "date_of_birth": "1990-01-01",
  "gender": "male",
  "address": "456 Đường DEF, Quận UVW, TP.HCM",
  "language_preference": "vi"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "Nguyễn Văn A",
    "email": "user@example.com",
    "phone": "+84901234567",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "address": "456 Đường DEF, Quận UVW, TP.HCM",
    "language_preference": "vi"
  },
  "error": null,
  "meta": null,
  "trace_id": "trace-profile-update",
  "timestamp": "2025-10-27T16:15:00Z"
}
```

### GET /api/v1/profile/promotions

**Response 200:**
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "code": "MEMBER20",
      "name": "Giảm 20% cho thành viên",
      "description": "Áp dụng cho tất cả dịch vụ, giá trị đơn tối thiểu 500,000đ",
      "discount_type": "percentage",
      "discount_value": 20,
      "min_amount": 500000,
      "valid_from": "2025-10-01T00:00:00Z",
      "valid_to": "2025-12-31T23:59:59Z",
      "remaining_uses": 3,
      "max_uses": 5
    },
    {
      "id": 2,
      "code": "WELCOME10",
      "name": "Giảm 10% cho khách hàng mới",
      "description": "Giảm 10% cho lần đặt lịch đầu tiên",
      "discount_type": "percentage",
      "discount_value": 10,
      "min_amount": 0,
      "valid_from": "2025-10-01T00:00:00Z",
      "valid_to": "2026-10-01T23:59:59Z",
      "remaining_uses": 1,
      "max_uses": 1
    }
  ],
  "error": null,
  "meta": null,
  "trace_id": "trace-promotions",
  "timestamp": "2025-10-27T16:20:00Z"
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Invalid request data",
  "data": null,
  "error": {
    "type": "ValidationError",
    "code": "VALIDATION_FAILED",
    "details": {
      "email": ["The email field is required."]
    }
  },
  "meta": null,
  "trace_id": "trace-error-001",
  "timestamp": "2025-10-27T16:25:00Z"
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated",
  "data": null,
  "error": {
    "type": "AuthenticationError",
    "code": "UNAUTHENTICATED",
    "details": {}
  },
  "meta": null,
  "trace_id": "trace-error-002",
  "timestamp": "2025-10-27T16:30:00Z"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Insufficient permissions",
  "data": null,
  "error": {
    "type": "AuthorizationError",
    "code": "UNAUTHORIZED",
    "details": {}
  },
  "meta": null,
  "trace_id": "trace-error-003",
  "timestamp": "2025-10-27T16:35:00Z"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found",
  "data": null,
  "error": {
    "type": "NotFoundError",
    "code": "NOT_FOUND",
    "details": {
      "resource": "Service",
      "id": 999
    }
  },
  "meta": null,
  "trace_id": "trace-error-004",
  "timestamp": "2025-10-27T16:40:00Z"
}
```

### 422 Unprocessable Entity (Validation)
```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "error": {
    "type": "ValidationError",
    "code": "VALIDATION_FAILED",
    "details": {
      "email": ["The email must be a valid email address."],
      "password": ["The password must be at least 8 characters."]
    }
  },
  "meta": null,
  "trace_id": "trace-error-005",
  "timestamp": "2025-10-27T16:45:00Z"
}
```

### 429 Too Many Requests
```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "data": null,
  "error": {
    "type": "RateLimitError",
    "code": "RATE_LIMIT_EXCEEDED",
    "details": {
      "retry_after": 30
    }
  },
  "meta": null,
  "trace_id": "trace-error-006",
  "timestamp": "2025-10-27T16:50:00Z"
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Internal server error",
  "data": null,
  "error": {
    "type": "InternalError",
    "code": "INTERNAL_ERROR",
    "details": {}
  },
  "meta": null,
  "trace_id": "trace-error-007",
  "timestamp": "2025-10-27T16:55:00Z"
}
```

---

## Notes

### Sử dụng với Postman/Mockoon
Có thể import các responses này vào Postman hoặc Mockoon để tạo mock API server:
- Tạo một mock server với base URL: `https://api.beautyclinic.com/v1`
- Thêm các mock responses cho từng endpoint
- Frontend có thể call API và nhận mock data

### Authentication
- Token format: `{user_id}|{random_string}`
- Header: `Authorization: Bearer {token}`
- Token có thể thay đổi tùy theo user_id để test với nhiều users khác nhau

### Pagination
- Mặc định: page=1, per_page=15
- Tối đa: per_page=100
- Meta object chứa thông tin pagination

### Localization
- Query param `locale` (vi, en, ja, zh)
- Mặc định: vi
- Response sẽ trả về content theo ngôn ngữ được chọn

---

**Last Updated:** 2025-10-27  
**Version:** 1.0  
**For Frontend Integration Testing**

