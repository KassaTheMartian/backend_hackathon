# API Documentation - Beauty Clinic System

## Base Information

**Base URL:** `https://api.beautyclinic.com/v1`
**Protocol:** HTTPS
**Format:** JSON
**Authentication:** Bearer Token (Laravel Sanctum)

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "OK",
  "data": { ... },
  "error": null,
  "meta": {
    "page": 1,
    "page_size": 15,
    "total_count": 120,
    "total_pages": 8,
    "has_next_page": true,
    "has_previous_page": false
  },
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "error": {
    "type": "ValidationError",
    "code": "VALIDATION_FAILED",
    "details": {
      "email": [
        "The email field is required."
      ],
      "password": [
        "The password must be at least 8 characters."
      ]
    }
  },
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

## HTTP Status Codes

| Code | Description |
|------|-------------|
| 200  | OK - Request successful |
| 201  | Created - Resource created successfully |
| 204  | No Content - Request successful, no data returned |
| 400  | Bad Request - Invalid request data |
| 401  | Unauthorized - Authentication required |
| 403  | Forbidden - No permission |
| 404  | Not Found - Resource not found |
| 422  | Unprocessable Entity - Validation error |
| 429  | Too Many Requests - Rate limit exceeded |
| 500  | Internal Server Error |

---

## 1. Authentication API

### 1.1 Register
**Endpoint:** `POST /auth/register`
**Auth Required:** No

**Request Body:**
```json
{
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "phone": "+84123456789",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "language_preference": "vi"
}
```

**Response:** 201 Created
```json
{
  "success": true,
  "message": "Registration successful. Please verify your email.",
  "data": {
    "user": {
      "id": 1,
      "name": "Nguyen Van A",
      "email": "user@example.com",
      "phone": "+84123456789",
      "language_preference": "vi",
      "email_verified_at": null
    },
    "token": "1|abc123xyz..."
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 1.2 Login
**Endpoint:** `POST /auth/login`
**Auth Required:** No

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "SecurePass123!",
  "device_name": "iPhone 13"
}
```

**Response:** 200 OK
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Nguyen Van A",
      "email": "user@example.com"
    },
    "token": "2|xyz789abc...",
    "token_type": "Bearer"
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 1.3 Logout
**Endpoint:** `POST /auth/logout`
**Auth Required:** Yes

**Response:** 200 OK
```json
{
  "success": true,
  "message": "Logged out successfully",
  "data": null,
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 1.4 Verify OTP
**Endpoint:** `POST /auth/verify-otp`
**Auth Required:** No

**Request Body:**
```json
{
  "phone_or_email": "user@example.com",
  "otp": "123456",
  "type": "email"
}
```

**Response:** 200 OK
```json
{
  "success": true,
  "message": "Verification successful",
  "data": null,
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 1.5 Forgot Password
**Endpoint:** `POST /auth/forgot-password`
**Auth Required:** No

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response:** 200 OK
```json
{
  "success": true,
  "message": "Password reset link sent to your email",
  "data": null,
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

---

## 2. Services API

### 2.1 List Services
**Endpoint:** `GET /services`
**Auth Required:** No

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| category_id | integer | Filter by category |
| is_featured | boolean | Filter featured services |
| min_price | decimal | Minimum price |
| max_price | decimal | Maximum price |
| locale | string | Language (vi, ja, en, zh) |
| page | integer | Page number |
| per_page | integer | Items per page (max: 100) |
| sort | string | Sort by (price, name, created_at) |
| order | string | asc or desc |

**Example Request:**
```
GET /services?category_id=1&locale=vi&page=1&per_page=15&sort=price&order=asc
```

**Response:** 200 OK
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
      "description": "Liệu trình điều trị mụn...",
      "short_description": "Giảm mụn hiệu quả",
      "price": 500000,
      "discounted_price": 450000,
      "duration": 60,
      "image": "https://cdn.example.com/services/acne-treatment.jpg",
      "rating": 4.5,
      "total_reviews": 120,
      "is_featured": true
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
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 2.2 Get Service Detail
**Endpoint:** `GET /services/{slug}`
**Auth Required:** No

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| locale | string | Language (vi, ja, en, zh) |

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "category": {
      "id": 1,
      "name": "Chăm sóc da mặt"
    },
    "name": "Điều trị mụn chuyên sâu",
    "slug": "dieu-tri-mun-chuyen-sau",
    "description": "<p>Full HTML description...</p>",
    "short_description": "Giảm mụn hiệu quả",
    "price": 500000,
    "discounted_price": 450000,
    "duration": 60,
    "image": "https://cdn.example.com/...",
    "gallery": [
      "https://cdn.example.com/gallery/1.jpg",
      "https://cdn.example.com/gallery/2.jpg"
    ],
    "rating": 4.5,
    "total_reviews": 120,
    "available_branches": [
      {
        "id": 1,
        "name": "Chi nhánh Quận 1",
        "address": "123 Nguyen Hue, Q1, TPHCM"
      }
    ],
    "related_services": [...],
    "reviews": [...]
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 2.3 List Service Categories
**Endpoint:** `GET /service-categories`
**Auth Required:** No

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| locale | string | Language |

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "name": "Chăm sóc da mặt",
      "slug": "cham-soc-da-mat",
      "description": "...",
      "icon": "fa-face",
      "services_count": 15
    }
  ],
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

---

## 3. Branches API

### 3.1 List Branches
**Endpoint:** `GET /branches`
**Auth Required:** No

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| locale | string | Language |
| latitude | decimal | User's latitude |
| longitude | decimal | User's longitude |

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "name": "Chi nhánh Quận 1",
      "slug": "chi-nhanh-quan-1",
      "address": "123 Nguyen Hue, Quan 1, TPHCM",
      "phone": "+84123456789",
      "email": "quan1@beautyclinic.com",
      "latitude": 10.7769,
      "longitude": 106.7009,
      "distance": 2.5,
      "opening_hours": {
        "monday": {"open": "09:00", "close": "18:00"},
        "tuesday": {"open": "09:00", "close": "18:00"}
      },
      "images": [...],
      "amenities": ["wifi", "parking", "wheelchair_access"]
    }
  ],
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 3.2 Get Branch Detail
**Endpoint:** `GET /branches/{id}`
**Auth Required:** No

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "name": "Chi nhánh Quận 1",
    "slug": "chi-nhanh-quan-1",
    "address": "123 Nguyen Hue, Quan 1, TPHCM",
    "phone": "+84123456789",
    "email": "quan1@beautyclinic.com",
    "latitude": 10.7769,
    "longitude": 106.7009,
    "opening_hours": {...},
    "description": "...",
    "images": [...],
    "amenities": [...],
    "available_services": [...],
    "staff": [...]
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 3.3 Get Available Time Slots
**Endpoint:** `GET /branches/{id}/available-slots`
**Auth Required:** No

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| date | date | Yes | Format: YYYY-MM-DD |
| service_id | integer | Yes | Service ID |
| staff_id | integer | No | Specific staff member |

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "date": "2025-10-28",
    "available_slots": [
      {
        "time": "09:00",
        "available": true,
        "staff": [
          {
            "id": 1,
            "name": "Dr. Nguyen Van A",
            "avatar": "..."
          }
        ]
      },
      {
        "time": "09:30",
        "available": true,
        "staff": [...]
      },
      {
        "time": "10:00",
        "available": false,
        "reason": "Fully booked"
      }
    ]
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

---

## 4. Bookings API

### 4.1 Create Booking
**Endpoint:** `POST /bookings`
**Auth Required:** Optional (Guest or Member)

**Request Body (Guest):**
```json
{
  "guest_name": "Tran Thi B",
  "guest_email": "guest@example.com",
  "guest_phone": "+84987654321",
  "branch_id": 1,
  "service_id": 1,
  "staff_id": 2,
  "booking_date": "2025-10-28",
  "booking_time": "10:00",
  "notes": "Tôi muốn tư vấn thêm về...",
  "promotion_code": "WELCOME10"
}
```

**Request Body (Member - with Bearer token):**
```json
{
  "branch_id": 1,
  "service_id": 1,
  "staff_id": 2,
  "booking_date": "2025-10-28",
  "booking_time": "10:00",
  "notes": "..."
}
```

**Response:** 201 Created
```json
{
  "success": true,
  "message": "Booking created successfully. Confirmation email sent.",
  "data": {
    "id": 123,
    "booking_code": "BK20251028001",
    "branch": {
      "id": 1,
      "name": "Chi nhánh Quận 1"
    },
    "service": {
      "id": 1,
      "name": "Điều trị mụn chuyên sâu",
      "price": 500000,
      "duration": 60
    },
    "staff": {
      "id": 2,
      "name": "Dr. Nguyen Van A"
    },
    "booking_date": "2025-10-28",
    "booking_time": "10:00",
    "status": "pending",
    "service_price": 500000,
    "discount_amount": 50000,
    "total_amount": 450000,
    "payment_status": "pending"
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 4.2 Get Booking Detail
**Endpoint:** `GET /bookings/{id}`
**Auth Required:** Yes (Owner or Guest with code)

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| booking_code | string | For guest access |

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 123,
    "booking_code": "BK20251028001",
    "customer": {
      "name": "Nguyen Van A",
      "email": "user@example.com",
      "phone": "+84123456789"
    },
    "branch": {...},
    "service": {...},
    "staff": {...},
    "booking_date": "2025-10-28",
    "booking_time": "10:00",
    "duration": 60,
    "status": "confirmed",
    "payment_status": "paid",
    "total_amount": 450000,
    "notes": "...",
    "created_at": "2025-10-27T10:30:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 4.3 List My Bookings
**Endpoint:** `GET /my-bookings`
**Auth Required:** Yes (Member)

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| status | string | Filter by status |
| page | integer | Page number |
| per_page | integer | Items per page |

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 123,
      "booking_code": "BK20251028001",
      "service": {
        "name": "Điều trị mụn chuyên sâu",
        "image": "..."
      },
      "branch": {
        "name": "Chi nhánh Quận 1"
      },
      "booking_date": "2025-10-28",
      "booking_time": "10:00",
      "status": "confirmed",
      "total_amount": 450000
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
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 4.4 Cancel Booking
**Endpoint:** `POST /bookings/{id}/cancel`
**Auth Required:** Yes

**Request Body:**
```json
{
  "cancellation_reason": "Tôi có việc đột xuất"
}
```

**Response:** 200 OK
```json
{
  "success": true,
  "message": "Booking cancelled successfully",
  "data": {
    "id": 123,
    "status": "cancelled",
    "cancellation_reason": "Tôi có việc đột xuất",
    "cancelled_at": "2025-10-27T15:00:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 4.5 Update Booking
**Endpoint:** `PUT /bookings/{id}`
**Auth Required:** Yes

**Request Body:**
```json
{
  "booking_date": "2025-10-29",
  "booking_time": "14:00",
  "notes": "Updated notes"
}
```

**Response:** 200 OK

---

## 5. Payments API

### 5.1 Create Payment Intent (Stripe)
**Endpoint:** `POST /payments/create-intent`
**Auth Required:** Yes

**Request Body:**
```json
{
  "booking_id": 123
}
```

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "client_secret": "pi_xxx_secret_yyy",
    "payment_intent_id": "pi_xxxxxxxxxxxxx",
    "amount": 450000,
    "currency": "vnd"
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 5.2 Confirm Payment
**Endpoint:** `POST /payments/confirm`
**Auth Required:** Yes

**Request Body:**
```json
{
  "booking_id": 123,
  "payment_intent_id": "pi_xxxxxxxxxxxxx",
  "payment_method": "stripe"
}
```

**Response:** 200 OK
```json
{
  "success": true,
  "message": "Payment confirmed successfully",
  "data": {
    "payment_id": 456,
    "booking_id": 123,
    "amount": 450000,
    "status": "completed",
    "paid_at": "2025-10-27T16:00:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 5.3 Stripe Webhook
**Endpoint:** `POST /payments/webhook`
**Auth Required:** No (Stripe signature verification)

**Headers:**
```
Stripe-Signature: xxx
```

---

## 6. Reviews API

### 6.1 List Reviews
**Endpoint:** `GET /reviews`
**Auth Required:** No

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| service_id | integer | Filter by service |
| rating | integer | Filter by rating (1-5) |
| page | integer | Page number |

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "user": {
        "name": "Nguyen Van A",
        "avatar": "..."
      },
      "service": {
        "id": 1,
        "name": "Điều trị mụn chuyên sâu"
      },
      "rating": 5,
      "title": "Dịch vụ tuyệt vời!",
      "comment": "Tôi rất hài lòng với kết quả...",
      "images": [...],
      "helpful_count": 15,
      "admin_response": "Cảm ơn bạn đã tin tưởng...",
      "created_at": "2025-10-20T10:00:00Z"
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
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 6.2 Create Review
**Endpoint:** `POST /reviews`
**Auth Required:** Yes (Member)

**Request Body:**
```json
{
  "booking_id": 123,
  "service_id": 1,
  "rating": 5,
  "title": "Dịch vụ tuyệt vời!",
  "comment": "Tôi rất hài lòng...",
  "service_quality_rating": 5,
  "staff_rating": 5,
  "cleanliness_rating": 5,
  "value_rating": 4,
  "images": [
    "base64_encoded_image_1",
    "base64_encoded_image_2"
  ]
}
```

**Response:** 201 Created
```json
{
  "success": true,
  "message": "Review submitted successfully. Waiting for approval.",
  "data": {
    "id": 50,
    "rating": 5,
    "is_approved": false
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

---

## 7. Blog API

### 7.1 List Posts
**Endpoint:** `GET /posts`
**Auth Required:** No

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| category_id | integer | Filter by category |
| locale | string | Language |
| page | integer | Page number |
| search | string | Search in title/content |

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "title": "10 Bí quyết chăm sóc da mùa hè",
      "slug": "10-bi-quyet-cham-soc-da-mua-he",
      "excerpt": "Mùa hè đến, làn da cần được chăm sóc...",
      "featured_image": "...",
      "category": {
        "id": 1,
        "name": "Chăm sóc da",
        "slug": "cham-soc-da"
      },
      "author": {
        "name": "Dr. Nguyen Van A"
      },
      "published_at": "2025-10-25T10:00:00Z",
      "reading_time": 5,
      "views_count": 1250
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
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 7.2 Get Post Detail
**Endpoint:** `GET /posts/{slug}`
**Auth Required:** No

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "title": "10 Bí quyết chăm sóc da mùa hè",
    "slug": "10-bi-quyet-cham-soc-da-mua-he",
    "content": "<p>Full HTML content...</p>",
    "featured_image": "...",
    "images": [...],
    "category": {...},
    "author": {...},
    "tags": ["chăm sóc da", "mùa hè", "làm đẹp"],
    "published_at": "2025-10-25T10:00:00Z",
    "reading_time": 5,
    "views_count": 1251,
    "related_posts": [...],
    "meta": {
      "title": "SEO title",
      "description": "SEO description"
    }
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

---

## 8. Contact API

### 8.1 Submit Contact Form
**Endpoint:** `POST /contact`
**Auth Required:** No

**Request Body:**
```json
{
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "phone": "+84123456789",
  "subject": "Tư vấn dịch vụ",
  "message": "Tôi muốn được tư vấn về..."
}
```

**Response:** 201 Created
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
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

---

## 9. Chatbot API

### 9.1 Send Message
**Endpoint:** `POST /chatbot/message`
**Auth Required:** Optional

**Request Body:**
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

**Response:** 200 OK
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
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 9.2 Get Chat History
**Endpoint:** `GET /chatbot/session/{sessionId}`
**Auth Required:** Optional

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "session_id": "sess_abc123xyz",
    "messages": [
      {
        "id": 1,
        "sender_type": "user",
        "message": "Tôi muốn đặt lịch hẹn",
        "created_at": "2025-10-27T10:00:00Z"
      },
      {
        "id": 2,
        "sender_type": "bot",
        "message": "Chào bạn! Tôi có thể giúp...",
        "created_at": "2025-10-27T10:00:05Z"
      }
    ]
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

---

## 10. User Profile API (Member Only)

### 10.1 Get Profile
**Endpoint:** `GET /profile`
**Auth Required:** Yes

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "user@example.com",
    "phone": "+84123456789",
    "avatar": "...",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "address": "...",
    "language_preference": "vi",
    "total_bookings": 15,
    "total_spent": 10500000,
    "member_since": "2024-01-15T00:00:00Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

### 10.2 Update Profile
**Endpoint:** `PUT /profile`
**Auth Required:** Yes

**Request Body:**
```json
{
  "name": "Nguyen Van A",
  "phone": "+84123456789",
  "date_of_birth": "1990-01-01",
  "gender": "male",
  "address": "123 Street, District, City",
  "language_preference": "vi"
}
```

**Response:** 200 OK

### 10.3 Change Password
**Endpoint:** `PUT /profile/password`
**Auth Required:** Yes

**Request Body:**
```json
{
  "current_password": "OldPass123!",
  "new_password": "NewPass123!",
  "new_password_confirmation": "NewPass123!"
}
```

**Response:** 200 OK

### 10.4 Get My Promotions
**Endpoint:** `GET /profile/promotions`
**Auth Required:** Yes

**Response:** 200 OK
```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 1,
      "code": "MEMBER20",
      "name": "Giảm 20% cho thành viên",
      "discount_type": "percentage",
      "discount_value": 20,
      "min_amount": 500000,
      "valid_from": "2025-10-01T00:00:00Z",
      "valid_to": "2025-12-31T23:59:59Z",
      "remaining_uses": 3
    }
  ],
  "error": null,
  "meta": null,
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

---

## 11. Rate Limiting

**Rate Limits:**
- Guest users: 60 requests/minute
- Authenticated users: 100 requests/minute
- Admin users: 200 requests/minute

**Rate Limit Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1635350400
```

**Rate Limit Exceeded Response:**
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
  "trace_id": "c5d5f3fa-5a7f-4c7a-9c7f-0b2a6e3f2f1e",
  "timestamp": "2025-10-27T09:09:29.844Z"
}
```

---

## 12. Pagination

All list endpoints support pagination:

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

**Meta Response:**
```json
{
  "meta": {
    "page": 1,
    "page_size": 15,
    "total_count": 100,
    "total_pages": 7,
    "has_next_page": true,
    "has_previous_page": false
  }
}
```

---

## 13. Error Codes

| Error Type | Code | Description |
|------------|------|-------------|
| ValidationError | VALIDATION_FAILED | Input validation failed |
| AuthenticationError | UNAUTHENTICATED | Authentication required |
| AuthorizationError | UNAUTHORIZED | Insufficient permissions |
| NotFoundError | NOT_FOUND | Resource not found |
| BusinessError | BOOKING_CONFLICT | Time slot already booked |
| PaymentError | PAYMENT_FAILED | Payment processing failed |
| RateLimitError | RATE_LIMIT_EXCEEDED | Too many requests |
| InternalError | INTERNAL_ERROR | Server error |
| BusinessError | INVALID_PROMOTION_CODE | Promotion code invalid or expired |
| BusinessError | BOOKING_NOT_CANCELLABLE | Booking cannot be cancelled |

---

**Version:** 1.0  
**Last Updated:** 2025-10-27
