## Tech Stack

- Backend: Laravel 12 (PHP ^8.2)
- Auth: Laravel Sanctum (API token/session), role-based checks in controllers
- API Docs: l5-swagger (OpenAPI annotations via `@OA`), generated with `php artisan l5-swagger:generate`
- Data Mapping: spatie/laravel-data (DTOs for requests/resources)
- Database: MySQL/MariaDB (default), PostgreSQL and SQL Server configs present; Eloquent ORM; migrations enabled
- Cache: Laravel Cache (default store: database), optional Redis/Memcached configs available
- Queue/Background: Laravel queue (configured runner in composer `dev` script)
- HTTP Client: Laravel Http client for external calls (Gemini API, VNPay)
- Realtime/Chat:
  - Chatbot: Google Gemini API (model `gemini-2.0-flash-exp`) via REST
  - Chat sessions/messages persisted in DB; resources for serialization
- Payments: VNPay integration (sandbox) with create/return/ipn/refund/query endpoints
- Localization: Middleware `SetLocale`, translations used across controllers/services; default timezone Asia/Ho_Chi_Minh
- Rate limiting: Global `throttle:api` and per-group `throttle:60,1`
- Build/Tooling:
  - Composer: phpunit, phpstan, pint, sail, pail
  - Node/Vite: Vite ^6, TailwindCSS ^4, laravel-vite-plugin; axios; concurrently for dev
- Logging/Observability: Standard Laravel logs; custom `ApiResponse` envelope with `trace_id` and timestamps
- Security: Validation via Form Requests, Sanctum-protected routes, error envelopes; sensitive admin endpoints not cached

Key Config References
- composer.json: Laravel 12, Sanctum, l5-swagger, spatie/laravel-data, PHP ^8.2
- package.json: Vite, Tailwind 4, axios, laravel-vite-plugin
- config/cache.php: default store `database`, other stores configured (redis, file, memcached)
- config/database.php: mysql (default), pgsql, mariadb, sqlsrv; Redis client `phpredis`
- config/chatbot.php: Gemini API settings (timeout, temperature, topK/topP, tokens), business context
- config/vnpay.php: VNPay sandbox settings (tmn_code, hash_secret, urls)
- routes/api.php: v1 API modules (auth, services, branches, posts, payments, chat, chatbot, profile, reviews)
