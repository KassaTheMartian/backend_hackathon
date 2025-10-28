Setup Guide

Prerequisites
- PHP 8.2+
- Composer
- MySQL/PostgreSQL
- Node.js 18+ (for Vite if needed)

Environment
1) Copy .env
   cp .env.example .env
2) Generate key
   php artisan key:generate
3) Configure DB in .env
   DB_DATABASE=beautyclinic
   DB_USERNAME=...
   DB_PASSWORD=...

Mail (required for OTP)
- Set MAIL_MAILER=smtp and SMTP creds (e.g. Mailtrap)

Gemini (Chatbot)
- GEMINI_API_KEY=your_key

VNPay (optional demo config)
- VNPAY_TMN_CODE=demo
- VNPAY_HASH_SECRET=secret
- VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
- VNPAY_RETURN_URL=http://localhost:8000/api/v1/payments/vnpay/return

Stripe (mocked)
- No keys required for hackathon; endpoints return simulated responses.

Install & Migrate
composer install
php artisan migrate --seed

Seeders
- Users: creates admin test user (email: admin@example.com, password: password)
- Services, Branches, Staff, Posts, Reviews, Bookings

Run
php artisan serve

API Auth
- Login: POST /api/v1/auth/login
- Register: POST /api/v1/auth/register
- OTP send: POST /api/v1/auth/send-otp (throttled)

Guest Booking Flow
- Send OTP: POST /api/v1/guest-booking/send-otp (throttled)
- Create booking with guest_email + guest_email_otp
- History: GET /api/v1/guest-bookings?guest_email&guest_email_otp

Reviews Moderation (admin)
- GET /api/v1/reviews/pending
- POST /api/v1/reviews/{id}/approve
- POST /api/v1/reviews/{id}/reject

Payments
- Stripe create-intent/confirm require ownership or admin
- VNPay endpoints simulated with signature checks

Setup (Local)

Requirements
- PHP 8.3+
- Composer
- MySQL 8+
- Node 18+ (for frontend assets if needed)

Steps
1. Copy env
```
cp .env.example .env
```
2. Configure DB and mail
- DB_* values
- MAIL_* (from address/name)
3. Keys & third-party
- GEMINI_API_KEY=your_key
- VNPAY_* (tmn_code, hash_secret, url, return_url) in config/vnpay.php
4. Install deps
```
composer install
php artisan key:generate
```
5. Migrate & seed
```
php artisan migrate --seed
```
6. Serve
```
php artisan serve
```
7. API docs (Swagger)
- Visit /api/documentation if l5-swagger is enabled, or see storage/api-docs/api-docs.json

Test Data
- Seeders create demo users, services, branches, and bookings.
- Use Auth endpoints to register and verify via OTP.

Notes
- For Chatbot (Gemini), suggestions fallback to local search if GEMINI_API_KEY is not set.
- VNPay endpoints simulate flows; ensure return and IPN URLs are reachable in your environment.

