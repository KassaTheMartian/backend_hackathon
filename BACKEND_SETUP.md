# Beauty Clinic Backend API - Setup Guide

## Tổng quan

Backend API cho hệ thống đặt lịch thẩm mỹ viện được xây dựng với Laravel 11, tuân theo kiến trúc Clean Architecture và API RESTful.

## Cấu trúc Project

```
app/
├── Http/
│   ├── Controllers/Api/V1/     # API Controllers
│   ├── Middleware/             # Custom Middleware
│   ├── Requests/               # Form Request Validation
│   └── Resources/              # API Resources
├── Models/                     # Eloquent Models
├── Services/                   # Business Logic Layer
├── Repositories/               # Data Access Layer
├── Jobs/                       # Background Jobs
├── Policies/                   # Authorization Policies
└── Exceptions/                 # Custom Exceptions
```

## Cài đặt

### 1. Cài đặt Dependencies

```bash
composer install
```

### 2. Cấu hình Environment

```bash
cp .env.example .env
php artisan key:generate
```

Cập nhật file `.env` với thông tin database và các service khác:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=beauty_clinic
DB_USERNAME=root
DB_PASSWORD=

# Stripe Payment
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret

# OpenAI Chatbot
OPENAI_API_KEY=your_openai_key

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
```

### 3. Chạy Migrations

```bash
php artisan migrate
```

### 4. Chạy Seeders

```bash
php artisan db:seed
```

### 5. Chạy Server

```bash
php artisan serve
```

## API Endpoints

### Authentication
- `POST /api/v1/auth/register` - Đăng ký
- `POST /api/v1/auth/login` - Đăng nhập
- `POST /api/v1/auth/logout` - Đăng xuất
- `GET /api/v1/auth/me` - Thông tin user

### Services
- `GET /api/v1/services` - Danh sách dịch vụ
- `GET /api/v1/services/{id}` - Chi tiết dịch vụ
- `GET /api/v1/service-categories` - Danh mục dịch vụ

### Branches
- `GET /api/v1/branches` - Danh sách chi nhánh
- `GET /api/v1/branches/{id}` - Chi tiết chi nhánh
- `GET /api/v1/branches/{id}/available-slots` - Lịch trống

### Bookings
- `GET /api/v1/bookings` - Danh sách đặt lịch
- `POST /api/v1/bookings` - Tạo đặt lịch
- `GET /api/v1/bookings/{id}` - Chi tiết đặt lịch
- `PUT /api/v1/bookings/{id}` - Cập nhật đặt lịch
- `POST /api/v1/bookings/{id}/cancel` - Hủy đặt lịch
- `GET /api/v1/my-bookings` - Đặt lịch của tôi

### Payments
- `POST /api/v1/payments/create-intent` - Tạo payment intent
- `POST /api/v1/payments/confirm` - Xác nhận thanh toán
- `POST /api/v1/payments/webhook` - Stripe webhook

### Reviews
- `GET /api/v1/reviews` - Danh sách đánh giá
- `POST /api/v1/reviews` - Tạo đánh giá
- `GET /api/v1/reviews/{id}` - Chi tiết đánh giá

### Posts
- `GET /api/v1/posts` - Danh sách bài viết
- `GET /api/v1/posts/{id}` - Chi tiết bài viết

### Contact
- `POST /api/v1/contact` - Gửi liên hệ

### Chatbot
- `POST /api/v1/chatbot/message` - Gửi tin nhắn
- `GET /api/v1/chatbot/session/{id}` - Lịch sử chat

### Profile
- `GET /api/v1/profile` - Thông tin profile
- `PUT /api/v1/profile` - Cập nhật profile
- `PUT /api/v1/profile/password` - Đổi mật khẩu
- `GET /api/v1/profile/promotions` - Khuyến mãi của tôi

## Response Format

Tất cả API responses đều tuân theo format chuẩn:

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

## Authentication

API sử dụng Laravel Sanctum cho authentication:

```bash
# Lấy token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'

# Sử dụng token
curl -X GET http://localhost:8000/api/v1/profile \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Rate Limiting

- Guest users: 60 requests/minute
- Authenticated users: 100 requests/minute
- Admin users: 200 requests/minute

## Testing

```bash
# Chạy tests
php artisan test

# Chạy specific test
php artisan test --filter ServiceTest
```

## Queue Jobs

Các job chạy background:

```bash
# Chạy queue worker
php artisan queue:work

# Chạy với Horizon (recommended)
php artisan horizon
```

## Monitoring

- **Logs**: `storage/logs/laravel.log`
- **Queue**: Laravel Horizon dashboard
- **API**: Request/Response logging enabled

## Deployment

1. Cập nhật `.env` cho production
2. Chạy migrations: `php artisan migrate --force`
3. Cache config: `php artisan config:cache`
4. Cache routes: `php artisan route:cache`
5. Cache views: `php artisan view:cache`
6. Optimize autoloader: `composer install --optimize-autoloader --no-dev`

## Các tính năng chính

✅ **Authentication & Authorization**
- User registration/login
- JWT tokens với Laravel Sanctum
- Role-based access control

✅ **Service Management**
- Quản lý dịch vụ đa ngôn ngữ
- Phân loại dịch vụ
- Tìm kiếm và lọc

✅ **Branch Management**
- Quản lý chi nhánh
- Tính toán khoảng cách
- Lịch trống theo thời gian

✅ **Booking System**
- Đặt lịch cho user và guest
- Quản lý trạng thái booking
- Tích hợp thanh toán

✅ **Payment Integration**
- Stripe payment gateway
- Webhook handling
- Refund support

✅ **Review System**
- Đánh giá dịch vụ
- Rating aggregation
- Admin moderation

✅ **Content Management**
- Blog posts đa ngôn ngữ
- SEO optimization
- Category management

✅ **Chatbot Integration**
- OpenAI GPT integration
- Session management
- Intent recognition

✅ **Background Jobs**
- Email notifications
- SMS alerts
- Data processing

## Lưu ý

- Tất cả timestamps đều sử dụng UTC
- API responses đều có trace_id để tracking
- Error handling được chuẩn hóa
- Rate limiting được áp dụng cho tất cả endpoints
- Logging được bật cho tất cả API requests

## Hỗ trợ

Nếu có vấn đề, vui lòng kiểm tra:
1. Logs trong `storage/logs/`
2. Database connections
3. Queue worker status
4. Environment variables
