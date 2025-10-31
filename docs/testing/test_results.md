## Test Results (Snapshot)

This file records a recent snapshot of results while developing tests. Update as needed during CI runs.

### Summary (Full Suite - Latest)
- Tests: 405
- Assertions: 1091
- Passed (approx): 366
- Failures: 33
- Errors: 6
- Skipped: 40
- Risky: 3

### By Suite
- Unit: 262 (OK)
- Feature: 143 (some failures/errors remain)

### How to Read This Report
- Summary: overall health snapshot (counts and distribution)
- By Suite: isolate whether issues are in logic (Unit) or integration (Feature)
- Sections below highlight areas with recent changes and their status

### Trends & Actions
- Recent stabilization of Unit suite (green)
- Outstanding Feature failures: Booking FK and legacy User/Exception endpoints
- Action items:
  - Fix Booking factories (FK safety) and endpoint payloads
  - Align legacy tests with current routes or skip them
  - Normalize error mapping assertions (404 endpoint vs resource)

### Unit - Repositories
- Status: PASS
- Files: 15 tests, assertions ~35
- Scope: Auth, Branch, Otp, Payment, Post, Promotion, Review, ServiceCategory, Service, Staff, Contact repositories
- Coverage highlights:
  - Filters/sorts/pagination glue verified (where applicable)
  - Safe finders (return null/empty) vs exceptions where required
  - Category/tag listings for posts/services return stable structures
- Next actions:
  - Add negative cases for invalid filter combinations
  - Add light performance assertions where indexes exist

### Unit - Services
- ChatbotServiceTest: PASS
  - Asserts context building (recent messages), Http::fake for LLM calls, role mapping (`assistant` vs `user`)
  - Verifies i18n and safe fallbacks
- ServiceServiceTest: PASS
  - Asserts list/create/update/delete, `findBySlug`, categories by locale, default flags (is_active)
- Next actions:
  - BookingService unit coverage (availability, promotion application) can be expanded
  - PaymentService edge cases around amount validation and idempotency already covered in unit; keep in sync with controller

### Feature
- BranchEndpointsTest: PASS
  - GET /branches (pagination, query validation), GET /branches/{id|slug}, GET /branches/{id}/available-slots (validation + success), GET /branches/{id}/staff
  - Envelope shape, status codes, and caching transparency validated (shape stable regardless of cache)
- PaymentEndpointsTest (VNPay): PASS (validation + reachability)
  - GET /payments (auth user scope), POST /payments/vnpay/create (payload validation), GET /payments/vnpay/return (reachable), POST /payments/vnpay/ipn (reachable), refund/query validations
  - Uses config and Http fakes where needed; no live gateway call
- ProfileEndpointsTest: PASS
  - GET/PUT /profile, PUT /profile/password, avatar upload/delete, language, stats, deactivate, promotions
  - Note: password path may result 200/400 depending on validation rules
- ServiceCategoriesTest: PASS
  - GET /service-categories; envelope and shape
- PostExtraEndpointsTest: PASS
  - GET /posts/featured, /post-categories, /post-tags; caching transparency
- ChatbotEndpointsTest: PASS
  - POST /chatbot (guest + auth), GET /chatbot/history; Http::fake Gemini; role mapping and is_bot flag asserted
- Next actions:
  - Booking endpoints require FK-safe factory usage and consistent update payloads (in progress elsewhere)
  - Align legacy User/Exception endpoint tests with current routes or de-scope

### Flakiness Watchlist
- None detected in latest run; continue to monitor payment-return timing-related tests (all faked)

Known failing areas (outside new tests)
- Legacy tests referencing Demo model and certain booking FK seeds may fail; not covered in this snapshot

### How to Reproduce
```bash
# Unit - Repos
php vendor/bin/phpunit --filter "Repositories"

# Unit - Services
php vendor/bin/phpunit --filter "ChatbotServiceTest|ServiceServiceTest"

# Feature - Selected
php vendor/bin/phpunit --filter "BranchEndpointsTest|PaymentEndpointsTest|ProfileEndpointsTest|ServiceCategoriesTest|PostExtraEndpointsTest|ChatbotEndpointsTest"
```


