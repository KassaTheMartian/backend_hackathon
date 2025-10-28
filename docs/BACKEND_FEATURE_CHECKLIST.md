Backend Feature Checklist (Aligned to Homepage → Booking → Auth → Content → Chat)

Legend: [x] implemented, [ ] missing, [~] partial/optional

1) Homepage (Backend support only)
- [x] Featured services API: GET /api/v1/services (supports pagination)
- [~] Highlighted content blocks (banners): [ ] endpoint (optional, CMS)
- [x] Quick booking support: availability + booking endpoints exist (see sections below)
- [x] Branch list with images: GET /api/v1/branches

2) Services Menu
- [x] Grid services list: GET /api/v1/services
- [x] Service detail page: GET /api/v1/services/{id}
- [x] Service categories: GET /api/v1/service-categories
- [ ] Additional filters (search/category/price): optional

3) Booking System
- [x] Form fields supported:
  - [x] Select service: service_id
  - [x] Select branch: branch_id
  - [x] Select staff: staff_id
  - [x] Date/time: booking_date, booking_time
  - [x] Notes, promotion code (optional)
- [x] Availability search: GET /api/v1/availability (Branch/Service/Date/Staff)
- [x] Create booking (member): POST /api/v1/bookings (auth:sanctum)
- [x] Create booking (guest): POST /api/v1/bookings + OTP verify (guest_email, guest_email_otp)
- [x] Guest OTP send: POST /api/v1/guest-booking/send-otp
- [x] Guest booking history: GET /api/v1/guest-bookings?guest_email&guest_email_otp
- [x] Member booking history: GET /api/v1/my-bookings (auth)
- [x] Booking detail (member): GET /api/v1/bookings/{id} (auth)
- [x] Update booking: PUT /api/v1/bookings/{id} (auth)
- [x] Cancel booking: POST /api/v1/bookings/{id}/cancel (auth)
- [x] Reschedule booking: POST /api/v1/bookings/{id}/reschedule (auth)
- [x] Email confirmation (job scaffolded): Jobs/SendBookingConfirmation.php
- [~] SMS confirmation: [ ] integration (optional)
- [x] OTP send rate limit and lockout threshold: recommended

4) Auth (Register/Login/OTP)
- [x] Register: POST /api/v1/auth/register (auto send email OTP)
- [x] Send verify OTP: POST /api/v1/auth/send-otp (purpose verify_email)
- [x] Verify OTP: POST /api/v1/auth/verify-otp (purpose verify_email)
- [x] Login: POST /api/v1/auth/login (blocks if email not verified)
- [x] Me: GET /api/v1/auth/me (auth)
- [x] Logout/Logout-all: POST /api/v1/auth/logout, POST /api/v1/auth/logout-all (auth)
- [x] Forgot/Reset password (token): POST /api/v1/auth/forgot-password, POST /api/v1/auth/reset-password
- [x] Reset password via OTP: POST /api/v1/auth/send-reset-otp, POST /api/v1/auth/reset-password-otp

5) Branches (Clinic Info)
- [x] List branches: GET /api/v1/branches
- [x] Branch detail: GET /api/v1/branches/{id}
- [x] Available slots (per branch): GET /api/v1/branches/{id}/available-slots
- [x] Map coordinates/phone/hours fields: ensure present in model/response as needed

6) Contact
- [x] Contact form submission: POST /api/v1/contact
- [ ] Anti-spam (rate limit/honeypot/captcha): optional
- [ ] Auto email acknowledgment: optional

7) Multilingual
- [~] Locale params supported on some endpoints (services/posts/categories)
- [ ] Complete i18n content strategy (translations/models): optional

8) Blog/News
- [x] List posts: GET /api/v1/posts (filters: category, tag, featured)
- [x] Post detail: GET /api/v1/posts/{id|slug}
- [x] Featured posts: GET /api/v1/posts/featured
- [ ] Categories/Tags endpoints (taxonomy browse): optional

9) Reviews & Feedback
- [x] List reviews: GET /api/v1/reviews (filters service/branch/rating)
- [x] Submit review (auth): POST /api/v1/reviews
- [x] Review detail: GET /api/v1/reviews/{id}
- [x] Admin approve/moderate reviews: implemented (required by brief)
- [x] Prevent duplicate reviews per booking/user: implemented (service-level guard)

10) Online Support (Chat)
- [x] Chat sessions/messages (auth):
  - GET/POST /api/v1/chatbot/sessions
  - GET /api/v1/chatbot/sessions/{id}
  - GET /api/v1/chatbot/sessions/{id}/messages
  - POST /api/v1/chatbot/sessions/{id}/messages (body: { message, mode?: booking|faq|human })
  - DELETE /api/v1/chatbot/sessions/{id}
  - DELETE /api/v1/chatbot/sessions/{id}/messages
- [x] Chatbot FAQ: FAQ mode answers booking/how-to
- [x] Chatbot booking suggestions: returns SUGGEST:{"services":[{"service_id","name"},...]}
- [x] Human escalation: standard acknowledgment (assign flow extendable)
- [ ] Guest chat (public routes): optional
- [ ] Async processing (queue) to avoid FE timeouts: optional quick win

11) Payments
- [x] Stripe: create intent, confirm, webhook
- [x] VNPay: create URL, return, IPN, refund, query
- [x] List payments (auth; scoped to user’s bookings)
- [x] Harden create-intent/confirm (auth or ownership/email checks)

12) Profile/Account
- [x] Profile show/update
- [x] Change password (422 on wrong current)
- [x] Avatar upload/delete
- [x] Language preference update
- [x] Stats, promotions
- [x] Deactivate/reactivate account

13) Operational & Quality
- [~] OpenAPI annotations across controllers (mostly present; ensure paths/security match routes)
- [x] setup.md: local run, keys (mail, Gemini, VNPay), seed data — required by brief
- [ ] Caching for featured/categories/common lists — optional
- [x] OTP rate limiting & attempt threshold — recommended
- [ ] Tests: expand features/unit — recommended

Notes
- Guest booking flows are protected by Email OTP, including history retrieval.
- Chatbot Gemini integration requires GEMINI_API_KEY in .env; service validates IDs against local catalog before suggesting.

