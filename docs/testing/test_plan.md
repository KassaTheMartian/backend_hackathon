## Test Plan

### Test Case Inventory (Overall)
- Total test cases: 405
- By test level:
  - Unit tests: 262
  - Feature/Integration tests: 143

### Breakdown by functional area (estimated)
- Booking: ~30
- Payments (VNPay): ~25
- Profile: ~20
- Service/Branch/Post: ~35
- Chatbot/Chat Real-time: ~20
- Repository/Service units: ~180
- Others (Exception handling, Example, Policy...): ~95

Note: Estimates are based on folder structure and last run; will be updated as tests are added.

This plan enumerates the main test scenarios (happy paths, validations, edge cases) per module. It complements existing tests under `tests/Feature` and `tests/Unit`.

### Conventions
- Use Given/When/Then structure in complex scenarios
- Include negative cases: invalid params, unauthorized, forbidden, not found, conflict
- For asynchronous/external flows (VNPay), use fakes and verify state transitions

### Auth
- Login/Register: valid credentials, invalid credentials, OTP flows
- Token revocation: current/all
- Profile access control (401/403)

### Services
- List services with filters/sorts; pagination meta
- Get by id/slug; 404 not found
- Categories: list and cache stability

### Branches
- List branches; show by id/slug
- Available slots: validations (date/service_id), happy path returns structure
- Branch staff listing

### Posts/Blog
- List posts with filters; featured; get by id/slug
- Categories/tags
- View count increment on show

### Reviews
- Public reads; create (auth) with validation; moderation (admin) approve/reject/respond

### Payments (VNPay)
- List payments (auth, user-scoped)
- Create payment: request payload validation
- Return & IPN: signature validation, status updates
- Refund & Query: payload validation, success/error mapping

Example (Given/When/Then)
- Given a pending booking and valid amount
- When client requests create VNPay URL with optional bank code
- Then service creates a pending payment record and returns signed URL
- And IPN with success marks payment completed and booking paid

### Profile
- Get/update profile; password change; avatar upload/delete
- Language update; stats; deactivate; promotions

### Chatbot
- POST /chatbot: guest and auth flows (Http::fake Gemini)
- GET /chatbot/history: guest by session_key; auth by user

Edge Cases
- Empty message; unsupported language; excessively long prompt; rate limits (if enforced)

### Error Handling
- 400 Validation error envelope shape
- 401/403 Authorization
- 404 Endpoint not found vs Resource not found
- 405 Method not allowed

Acceptance Criteria
- Standard envelope keys in all error cases
- Correct `type` and `code` mappings per error class

### Performance Checks (Lightweight)
- Pagination metadata present; essential indexes exist (if asserted in tests)

### Data Integrity
- Repositories: filter/sort composition; safe not-found returns; basic stats methods

---

## Test Data Strategy
- Prefer factories to generate minimal fixtures per test
- Use Http::fake for third-parties
- Avoid global seeders in Feature tests to reduce flakiness

### Data Matrices (examples)
- Booking: combinations of date/time/staff availability; promotion code present/absent/invalid
- Payments: response codes (00 success, 24 failed, invalid signature), amount mismatches

### Coverage Targets
- Unit: critical services (>90%), repositories (>80%)
- Feature: core endpoints happy path + primary validation paths

## Exit Criteria
- All unit tests pass locally and in CI
- Core feature tests pass for critical modules (Auth, Services, Branches, Payments, Profile, Chatbot)
- No flaky tests in two consecutive CI runs


