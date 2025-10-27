# ⚡ Beauty Clinic - Quick Reference

Quick reference guide cho developers.

---

## 📋 Files Reference

| File | Purpose |
|------|---------|
| `CODING_CHECKLIST.md` | **Full checklist** - Kiểm tra từng chức năng khi code |
| `MOCK_API_RESPONSES.md` | Mock responses cho Frontend |
| `MOCK_API_SETUP_GUIDE.md` | Hướng dẫn setup mock API |
| `API_DOCUMENTATION.md` | API documentation đầy đủ |
| `Beauty_Clinic_API.postman_collection.json` | Postman collection |

---

## 🎯 Core Modules Summary

### 1. Authentication (5 endpoints)
```
POST /auth/register -> OK
POST /auth/login -> OK
POST /auth/logout
GET  /auth/me
POST /auth/forgot-password
POST /auth/reset-password
```

### 2. Services (4 endpoints)
```
GET    /services
GET    /services/:id
GET    /service-categories
POST   /services        (Admin)
PUT    /services/:id    (Admin)
DELETE /services/:id    (Admin)
```

### 3. Branches (4 endpoints)
```
GET /branches
GET /branches/:id
GET /branches/:id/available-slots
[Admin CRUD]
```

### 4. Bookings (6 endpoints)
```
GET  /bookings
POST /bookings
GET  /bookings/:id
PUT  /bookings/:id
POST /bookings/:id/cancel
GET  /my-bookings
```

### 5. Payments (3 endpoints)
```
POST /payments/create-intent
POST /payments/confirm
POST /payments/webhook
```

### 6. Reviews (4 endpoints)
```
GET /reviews
POST /reviews
GET /reviews/:id
[Admin approve/reject]
```

### 7. Profile (5 endpoints)
```
GET /profile
PUT /profile
PUT /profile/password
POST /profile/avatar
DELETE /profile/avatar
GET /profile/promotions
```

### 8. Blog (3 endpoints)
```
GET /posts
GET /posts/:slug
GET /posts/featured
```

### 9. Contact (1 endpoint)
```
POST /contact
```

### 10. Chatbot (5 endpoints)
```
POST   /chatbot/message
GET    /chatbot/sessions
POST   /chatbot/sessions
GET    /chatbot/sessions/:id
DELETE /chatbot/sessions/:id
```

---

## 🔑 Key Features to Remember

### Booking Flow
1. User browses services → Select service
2. Choose branch → Check available slots
3. Choose date/time/staff → Apply promotion (optional)
4. Create booking → Generate booking code
5. Process payment → Confirm booking
6. Send confirmation email

### Payment Flow
1. Create payment intent (Stripe)
2. User confirms payment (Frontend)
3. Backend confirms via webhook
4. Update booking payment_status
5. Send receipt email

### Review Flow
1. User completes booking
2. User submits review (pending approval)
3. Admin reviews/approves
4. Auto-approve after 24h if no action
5. Update service/staff ratings

---

## 📊 Database Models

### Core Models
```
User
├── bookings (HasMany)
├── reviews (HasMany)
├── promotion_usages (HasMany)
└── chat_sessions (HasMany)

Service
├── category (BelongsTo)
├── branches (BelongsToMany)
├── staff (BelongsToMany)
├── bookings (HasMany)
└── reviews (HasMany)

Branch
├── services (BelongsToMany)
├── staff (HasMany)
└── bookings (HasMany)

Booking
├── user (BelongsTo)
├── branch (BelongsTo)
├── service (BelongsTo)
├── staff (BelongsTo)
├── payment (HasOne)
├── reviews (HasMany)
└── status_history (HasMany)

Payment
└── booking (BelongsTo)

Review
├── user (BelongsTo)
├── booking (BelongsTo)
├── service (BelongsTo)
├── staff (BelongsTo)
└── branch (BelongsTo)

Promotion
└── usages (HasMany)

Staff
├── user (BelongsTo)
├── branch (BelongsTo)
├── services (BelongsToMany)
├── bookings (HasMany)
└── reviews (HasMany)
```

---

## 🎨 Response Format

### Success
```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "error": null,
  "meta": { "page": 1, "page_size": 15, "total_count": 100 },
  "trace_id": "xyz",
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
  "trace_id": "xyz",
  "timestamp": "2025-10-27T10:00:00Z"
}
```

---

## 🔐 Authentication

### Token Format
```
Bearer {token}

Where token = "{user_id}|{random_string}"
```

### Required Headers
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## 📝 Validation Rules

### Common Rules
- Email: `required|email|max:255`
- Password: `required|string|min:8`
- Phone: `required|string|max:20`
- Name: `required|string|max:255`
- URL: `nullable|url|max:500`
- Date: `required|date|after_or_equal:today`
- Time: `required|date_format:H:i`
- Image: `image|mimes:jpeg,png,jpg,webp|max:2048`

### Booking Rules
- booking_date: Not in past
- booking_time: Within business hours
- Slot: Not already booked
- Promotion: Valid and applicable

---

## ⚡ Quick Tips

### When Creating Booking
```php
1. Validate input
2. Check slot availability
3. Validate promotion code
4. Calculate total
5. Create booking
6. Create status history
7. Send email
8. Return response
```

### When Processing Payment
```php
1. Create payment intent
2. Store intent_id
3. Return client_secret
4. User confirms on Frontend
5. Backend receives webhook
6. Verify payment
7. Update booking
8. Send email
```

### Status Transitions
```
Booking: pending → confirmed → completed
Booking: pending/confirmed → cancelled
Review: pending → approved
Payment: pending → completed → refunded
Contact: new → in_progress → resolved
```

---

## 🚨 Common Errors

### Handle These Cases
- Slot already booked
- Promotion invalid or expired
- Payment failed
- User not authenticated
- Insufficient permissions
- Resource not found
- Validation errors
- Rate limit exceeded

---

## 📦 Dependencies to Remember

### Key Packages
- Laravel Sanctum (Auth)
- Stripe (Payments)
- Mail (Notifications)
- Queue (Async jobs)
- Redis (Cache/Session)
- Image Intervention (Image handling)

### Config Files
- `config/sanctum.php` - Auth
- `config/services.php` - Stripe
- `config/mail.php` - Email
- `config/queue.php` - Queue
- `config/cache.php` - Cache

---

## 🎯 Priority Checklist

### Phase 1: Core Features
- [ ] Authentication
- [ ] Services listing
- [ ] Branch listing
- [ ] Booking creation
- [ ] Payment processing
- [ ] Profile management

### Phase 2: Advanced Features
- [ ] Reviews
- [ ] Chatbot
- [ ] Blog
- [ ] Contact form
- [ ] Promotions

### Phase 3: Admin & Optimization
- [ ] Admin dashboard
- [ ] Reports
- [ ] Caching
- [ ] Performance optimization
- [ ] API documentation

---

## 📞 Key Endpoints to Test

### For Manual Testing
1. **Register → Login → Get Profile**
2. **Browse Services → Get Service Detail**
3. **Check Available Slots → Create Booking**
4. **Process Payment → Confirm Booking**
5. **Submit Review**
6. **Update Profile**

### For Integration Testing
1. Complete booking flow
2. Payment webhook handling
3. Email notifications
4. Promotion application
5. Review approval workflow

---

**Keep this file handy while coding!** 🚀

