## Local Development Setup (Backend)

This guide explains how to run the Laravel backend locally on Windows/macOS/Linux.

### 1) Prerequisites
- PHP 8.2+
- Composer 2.x
- Node.js 18+ and npm
- Git
- Database: MySQL 8+ (recommended) or SQLite for quick start

Optional:
- Redis (if you switch cache/queue stores)

### 2) Clone & Install
```bash
git clone <repo-url>
cd backend_hackathon
composer install
npm install
```

### 3) Environment
Create `.env` from the example and generate app key:
```bash
copy .env.example .env   # PowerShell (Windows)
# cp .env.example .env   # macOS/Linux
php artisan key:generate
```

Minimal `.env` for quick start with SQLite:
```
APP_NAME="AI Hackathon"
APP_ENV=local
APP_KEY=base64:GENERATED_BY_KEY_GENERATE
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CACHE_STORE=database
QUEUE_CONNECTION=sync

# Gemini (Chatbot) – supply a valid key
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent
GEMINI_MODEL=gemini-2.0-flash-exp
GEMINI_API_VERSION=v1beta
GEMINI_TIMEOUT=30
GOOGLE_API_KEY=your_gemini_api_key_here

# VNPay (sandbox): keep defaults or replace
VNPAY_TMN_CODE=TDCER7JD
VNPAY_HASH_SECRET=changeme
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_RETURN_URL=http://localhost:3000/account/profile
VNPAY_API_URL=https://sandbox.vnpayment.vn/merchant_webapi/api/transaction
```

Create the SQLite file (if using SQLite):
```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

For MySQL instead, set in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hackathon
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4) Database Migrations & Seed
```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

Notes:
- Cache store default is `database`; the `cache` table migration exists and runs with `migrate`.
- Seeders populate demo data (branches, services, posts, staff, etc.).
- `storage:link` creates a symbolic link from `public/storage` to `storage/app/public` for file uploads. This is crucial for displaying images and uploaded files in the frontend.

### 5) Run the App

Option A: One-liner dev script (PHP server + queue + logs + Vite)
```bash
composer run dev
```

Option B: Separate terminals
```bash
php artisan serve --host=127.0.0.1 --port=8000
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

Visit API at: `http://127.0.0.1:8000`

### 6) API Docs (Swagger)
Generate and view OpenAPI docs:
```bash
php artisan l5-swagger:generate
```
The JSON is saved to `storage/api-docs`. If Swagger UI is enabled, open `/api/documentation` (depends on l5-swagger config).

### 7) Useful Commands
- Clear caches: `php artisan optimize:clear`
- Run tests: `phpunit` or `php artisan test`
- Fix code style: `vendor/bin/pint`
- Static analysis: `composer phpstan`

### 8) Common Issues
- Port in use: change with `php artisan serve --port=8001`
- Missing SQLite file: re-run the `touch database/database.sqlite`
- 500 errors: check `storage/logs/laravel.log`
- Gemini errors: ensure `GOOGLE_API_KEY` is set and firewall allows outbound HTTPS
- VNPay callbacks: set `VNPAY_RETURN_URL` to a reachable URL if testing end-to-end

### 9) Switching Stores (Optional)
To use Redis for cache/queue:
```
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### 10) Directory Quick Links
- Routes: `routes/api.php`
- Config: `config/*.php` (cache, database, chatbot, vnpay)
- Migrations/Seeders: `database/migrations`, `database/seeders`
- Logs: `storage/logs/laravel.log`


