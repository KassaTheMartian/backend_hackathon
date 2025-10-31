## Source Tree (Backend)

This document outlines the repository layout and explains what each area is responsible for, so new contributors can navigate quickly.

### Project root
- app/: Application code (controllers, services, models, DTOs, etc.)
- bootstrap/: Laravel bootstrap and cached manifests
- config/: All configuration (framework + app-specific like chatbot, vnpay)
- database/: Migrations, factories, seeders, local sqlite db
- docs/: API specifications and architecture docs
- public/: Web entrypoint (index.php) and public assets
- resources/: Frontend assets (Vite), Blade views, i18n resources
- routes/: Route definitions (API v1 lives in routes/api.php)
- storage/: Logs, framework caches, generated swagger json
- tests/: PHPUnit tests (Feature + Unit)
- vendor/: Composer dependencies
- Root files: composer.json, package.json, vite.config.js, phpunit.xml, phpstan.neon, artisan, README.md

### app/ (application code)
- Console/
  - Commands/...: Custom Artisan commands (if any in future)
- Data/ (DTOs)
  - Booking/, Branch/, Chat/, Review/, Service/, User/: Strongly-typed request/response data objects using spatie/laravel-data.
- Exceptions/
  - BaseException.php, BusinessException.php: Domain-level error types
  - Handler.php: Centralized exception handling
  - ResourceNotFoundException.php: 404 flow helper
- Http/
  - Controllers/
    - Api/V1/...: REST controllers by domain: Auth, Service, Branch, Booking, Payment, Review, Post, Contact, Chatbot, ChatRealTime, Profile, Staff
  - Middleware/: Cross-cutting concerns (e.g., SetLocale, throttling, auth)
  - Requests/: FormRequest validators per endpoint (e.g., ChatRequest, SendMessageRequest)
  - Resources/: API transformers (e.g., ChatMessageResource, BranchResource)
  - Responses/: `ApiResponse` envelope (success/error/trace_id)
- Jobs/
  - SendBookingConfirmation.php: Queue job for booking emails
- Logging/
  - DailyJsonFormatter.php: Structured log formatting
- Mail/
  - OtpMail.php, BookingConfirmationMail.php, WelcomeMail.php
- Models/
  - Eloquent models: Booking, Branch, ChatMessage, ChatSession, Payment, Post, PostCategory, PostTag, Review, Service, ServiceCategory, Staff, User, etc.
- OpenApi.php: L5-Swagger bootstrapping for annotations
- Policies/: Authorization policies (e.g., UserPolicy)
- Providers/
  - AppServiceProvider.php: Container bindings (repositories/services), app boot
  - RouteServiceProvider.php: Route configuration
- Repositories/
  - Contracts/: Repository interfaces
  - Eloquent/: Implementations using Eloquent ORM
- Rules/
  - Translatable.php: Custom validation rule for i18n fields
- Services/
  - Contracts/: Service interfaces
  - Domain services: AuthService, BookingService, BranchService, ChatbotService (Gemini), ChatRealTimeService, ContactService, LoggingService, PaymentService (VNPay), PostService, ProfileService, PromotionService, ReviewService, ServiceService, ServiceCategoryService, StaffService
- Traits/
  - HasLocalization.php: Helpers for locale-aware fields

### config/
- app.php: App name, env, timezone (Asia/Ho_Chi_Minh), locales
- cache.php: Default store database (also supports redis/file/memcached)
- chatbot.php: Gemini model, generation config, business context text
- database.php: MySQL (default) + PostgreSQL/SQL Server configs; Redis client phpredis
- l5-swagger.php: Swagger generation settings
- rate_limiting.php: Global API throttles
- sanctum.php: Sanctum API auth
- services.php: External services keys (e.g., Gemini)
- vnpay.php: VNPay integration endpoints and keys (sandbox)

### routes/
- api.php: API v1 routes grouped with `SetLocale` and throttling (60 rpm). Includes public and auth-protected segments, chat real-time and chatbot routes, VNPay flows.
- web.php: Web routes (if any views)
- console.php: Scheduled/console commands

### database/
- migrations/: Schema evolution (tables for bookings, branches, chat, payments, posts, reviews, services, staff, users, etc.)
- factories/: Test data factories per model
- seeders/: Seed data for all modules (DatabaseSeeder orchestrates)
- database.sqlite: Local dev DB (created by composer post-create script)

### docs/
- api/: Public API docs by domain (follow API_TEMPLATE)
- api_admin/: Admin-only API docs (moderation, payments)
- architecture/: `techstack.md`, `source_tree.md`
- API_TEMPLATE.md: Shared spec template

### tests/
- Feature/: Endpoint-level tests (HTTP responses, envelopes)
- Unit/: Unit tests (services, rules); `TestCase.php` base

---

## How things fit together (explained)
- Request lifecycle: Route → Controller (validates via FormRequest) → Service (business logic) → Repository (DB via Eloquent) → Resource transforms → `ApiResponse` envelope.
- Auth: Public endpoints open; protected endpoints use Sanctum middleware; admin/staff checks inside controllers/services.
- Caching: Controllers cache read endpoints (lists/details) with Cache::remember; keys include locale and query to prevent collisions; TTL varies by endpoint.
- Swagger: Controllers include `@OA` annotations. Run `php artisan l5-swagger:generate` to output JSON under `storage/api-docs`.
- Internationalization: `SetLocale` middleware sets locale from request; `HasLocalization` and translation files in `resources/lang` used by services/resources.
- Chatbot: `ChatbotService` builds context (branches/services), calls Gemini API via Laravel HTTP client, persists conversation in `ChatSession`/`ChatMessage`.
- Real-time chat (simulated via REST): `ChatRealTimeController` manages guest sessions, messages, transfers to staff, and polling for new messages.
- Payments: `PaymentService` integrates VNPay (sandbox) for create/return/ipn/refund/query; routes in `routes/api.php`.

## Conventions
- Controllers thin; Services hold business logic; Repositories abstract persistence.
- Validation via FormRequests in `app/Http/Requests`.
- Responses use Resources in `app/Http/Resources` and standard envelope with `trace_id`.
- DTOs in `app/Data` for safer typing at boundaries (spatie/laravel-data).

## Where to add new features
- Route: `routes/api.php`
- Request validation: `app/Http/Requests/<Domain>`
- Controller: `app/Http/Controllers/Api/V1/<Domain>Controller.php`
- Service: `app/Services/<Domain>Service.php` (+ interface in `Services/Contracts`)
- Repository: `app/Repositories/Eloquent/...` (+ interface in `Repositories/Contracts`)
- Resource: `app/Http/Resources/<Domain>`
- Docs: `docs/api/<domain>/<endpoint>.md` following `docs/API_TEMPLATE.md`

