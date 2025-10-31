## Testing Strategy

### Test Case Summary (Current)
- Total tests: 405 (Feature + Unit)
- By suite:
  - Unit: 262
  - Feature: 143
- Last full run status:
  - Passed (OK): 366
  - Failed: 33
  - Errors: 6
  - Skipped: 40
  - Risky: 3

Note: Numbers come from the latest CI/local run and may change as tests evolve.

This document outlines the overall testing approach for the backend: scope, levels, methods, areas covered, and non-goals. It maps our test types to code locations and CI usage.

### Principles
- Small, fast, reliable, deterministic tests
- Clear separation of concerns: Unit vs Feature/Integration
- Minimize global state and cross-test coupling
- Fail with signal: assertions should explain what broke and why

### Testing Pyramid (focus order)
1) Unit tests (business logic, repositories, services)
2) Feature/API tests (HTTP flow, middleware, controllers, resources)
3) End-to-end/manual checks where strictly necessary

### Naming & Structure Conventions
- File names mirror SUT: `ServiceServiceTest.php`, `BranchEndpointsTest.php`
- One responsibility per test method, use descriptive test names
- Arrange-Act-Assert structure; prefer Given/When/Then comments if lengthy

### Goals
- Ensure endpoint contracts are stable (status codes, envelopes, schemas)
- Validate business logic and edge cases in services and repositories
- Keep integration with third-parties (VNPay, Gemini) reliable via fakes/mocks
- Enable fast feedback for contributors, with deterministic tests

### Test Levels
- Unit Tests (UT)
  - Location: `tests/Unit/**`
  - Scope: Single class or small cluster without external side effects
  - Techniques: Mock collaborators; assert method outputs/side effects
  - Examples: Services (e.g., ServiceService, ChatbotService), Repositories (query shards)
- Feature/Integration Tests (IT)
  - Location: `tests/Feature/**`
  - Scope: HTTP endpoints end-to-end through routing, middleware, controller, service, DB
  - Techniques: In-memory or test DB; real serialization/envelope; Http::fake for externals
  - Examples: Branch endpoints, Payments VNPay routes, Profile endpoints, Chatbot endpoints

### What We Test
- Response envelope consistency: `success`, `message`, `data`, `error`, `meta`, `trace_id`, `timestamp`
- Happy path for each public endpoint (200/201)
- Validation and auth paths (400/401/403/422) where applicable
- Typical read caching does not change the shape (assert OK regardless of cache)
- Repositories: pagination, filtering, sorting glue; safe finders; query composition
- Services: business rules (defaults, derived fields), external calls via fakes

### What We Don't Test
- UI rendering
- Live calls to external providers in CI (VNPay, Gemini): replaced by Http::fake
- Performance micro-benchmarks in CI; only structural assertions exist

### Test Data
- Factories for models under `database/factories`
- Seeders for larger scenario testing; Feature tests aim to create local data minimally to avoid slow seeds

### Caching & Idempotency
- Caching should not alter response shape; include cases where cache is warm/cold
- Payment flows assert idempotency where gateway reports repeated success

### Internationalization
- Ensure endpoints behave consistently across locales; validate `language`/locale parameters where present

### Observability
- Tests should avoid asserting on logs unless required; prefer behavior over side effects

### Tooling
- PHPUnit 11
- Laravel testing helpers (TestResponse, actingAs, Storage::fake, Http::fake)
- Mockery for unit tests

### Runbook
- Run all tests: `php vendor/bin/phpunit -d memory_limit=1G --colors=always`
- Run a single class: `php vendor/bin/phpunit --filter BranchEndpointsTest`
- Clear caches before rerun if needed: `php artisan optimize:clear`

### CI Considerations
- Use SQLite or MySQL test DB
- Ensure `.env.testing` has fast stores (array/file cache if desired)
- Disable external calls (Http::fake default in CI bootstrap if needed)

### Flakiness Prevention
- No real time-dependent external calls; freeze time if necessary
- Avoid random data where it impacts assertions; pin with states

### Exit Criteria
- Unit suite green; core Feature flows green (Auth, Services, Branches, Payments, Profile, Chatbot)
- Two consecutive green runs without flaky test quarantines


