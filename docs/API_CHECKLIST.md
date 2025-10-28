# Backend API Checklist (Based on de_bai.md and Current Source)

## Auth
- [x] Login/Register (email verify required for login)
- [x] Send/Verify OTP for email verification
- [x] Forgot/Reset password (token + OTP)
- [x] Me/Logout/Logout-all (Sanctum)

## Services
- [x] List services (pagination)
- [x] Service detail
- [x] Service categories
- [ ] Additional filters (search/category/price) — optional

## Branches
- [x] List branches
- [x] Branch detail
- [x] Available time slots

## Booking
- [x] Create booking (member)
- [x] Create booking (guest with email OTP)
- [x] My bookings (member)
- [x] Get booking by id (member)
- [x] Update/Cancel/Reschedule (member)
- [x] Availability search
- [x] Guest booking: send OTP
- [x] Guest booking history by email + OTP (public)
- [ ] OTP rate-limit & attempt lockout threshold — recommended

## Payments
- [x] Stripe: create intent, confirm, webhook
- [x] VNPay: create, return, IPN, refund, query
- [x] List payments (auth, scoped to user)
- [ ] Secure create/confirm (auth or booking ownership/email checks) — recommended

## Reviews
- [x] List reviews (filters)
- [x] Create review (member)
- [x] Review detail
- [ ] Prevent duplicate review per booking/user — recommended
- [ ] Moderation/admin endpoints — optional

## Posts/Blog
- [x] List posts (filters)
- [x] Post detail (id or slug)
- [x] Featured posts
- [ ] Categories/Tags endpoints — optional

## Contact
- [x] Create contact submission
- [ ] Anti-spam (rate limit/honeypot/captcha) — optional
- [ ] Email acknowledgment — optional

## Profile
- [x] Get/Update profile
- [x] Change password (422 on wrong current)
- [x] Avatar upload/delete
- [x] Update language preference
- [x] Stats/Promotions
- [x] Deactivate/Reactivate

## Chatbot
- [x] Sessions and messages (auth)
- [x] Modes: faq, booking suggestions, human escalation
- [x] Gemini integration (fixed output schema via `SUGGEST:{...}`)
- [ ] Guest chatbot (public routes) — optional
- [ ] Async processing (queue) to avoid FE timeout — optional

## Multilingual
- [~] Locale support in services/posts/categories (partial)
- [ ] Full i18n strategy (models/translations) — optional

## Docs/Testing/Deployment
- [~] Swagger annotations (ensure all paths/security aligned)
- [ ] setup.md (local setup, keys, data) — required by de_bai
- [ ] High-level architecture/API docs — recommended
- [ ] Deployment notes — optional
- [ ] Expand automated tests — recommended

## Hardening/Performance
- [ ] OTP send rate-limit & global attempt threshold
- [ ] Align route protection for payments create/confirm (auth) or add extra checks
- [ ] Cache: featured posts, categories, common lists
- [ ] Image handling for review images (multipart/storage) if needed


