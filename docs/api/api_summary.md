## API Summary (v1)

This file summarizes public and protected endpoints exposed by the backend (`/api/v1`). It also notes auth requirements and brief descriptions.

Base URL: `https://api.example.com/api/v1`

---

### Authentication
- POST `/auth/login`
- POST `/auth/register`
- POST `/auth/verify-otp`
- POST `/auth/send-reset-otp` (rate limited: otp)
- POST `/auth/reset-password-otp`
- POST `/auth/test-email`
- GET `/auth/me` (auth)
- POST `/auth/logout` (auth)
- POST `/auth/logout-all` (auth)

---

### Services
- GET `/services` – list services (paginated)
- GET `/services/{id}` – get by id or slug
- GET `/service-categories` – list categories

Caching: list (5m), detail (15m), categories (60m)

---

### Branches
- GET `/branches` – list branches (paginated)
- GET `/branches/{id}` – get by id or slug
- GET `/branches/{id}/available-slots` – available time slots (realtime, no cache)
- GET `/branches/{branch}/staff` – staff by branch

Caching: list (5m), detail (15m)

---

### Staff
- GET `/staff` – list staff

---

### Reviews
- GET `/reviews` – list reviews
- GET `/reviews/{id}` – review detail
- POST `/reviews` (auth) – create
- GET `/reviews/pending` (auth) – list pending (admin/mod)
- POST `/reviews/{id}/approve` (auth)
- POST `/reviews/{id}/reject` (auth)
- POST `/reviews/{id}/respond` (auth)

---

### Posts (Blog)
- GET `/posts` – list posts (filters: category, tag, featured)
- GET `/posts/featured` – featured posts (limit)
- GET `/posts/{id}` – get by id or slug
- GET `/post-categories` – list post categories
- GET `/post-tags` – list post tags

Caching: list (5m), detail (15m), featured (15m), categories/tags (60m)

---

### Contact
- POST `/contact` – submit contact form

---

### Bookings
- POST `/bookings` (auth) – create booking
- PUT `/bookings/{id}` (auth) – update booking
- GET `/my-bookings` (auth) – my bookings
- GET `/bookings/by-code/{code}` (auth) – lookup by code
- POST `/bookings/{id}/cancel` – cancel booking
- POST `/bookings/{id}/reschedule` – reschedule booking
- GET `/availability` – general availability
- POST `/guest-booking/send-otp` – send OTP (rate limited: otp)
- GET `/guest-bookings` – guest booking list
- POST `/create-guest-bookings` – create guest bookings

---

### Payments
- GET `/payments` (auth) – list payments (scoped to user)
- POST `/payments/vnpay/create` – create VNPay payment
- GET `/payments/vnpay/return` – VNPay return
- POST `/payments/vnpay/ipn` – VNPay IPN
- POST `/payments/vnpay/refund` – refund
- POST `/payments/vnpay/query` – query transaction

---

### Profile
- GET `/profile` (auth) – profile details
- PUT `/profile` (auth) – update profile
- PUT `/profile/password` (auth) – change password
- POST `/profile/avatar` (auth) – upload avatar
- DELETE `/profile/avatar` (auth) – delete avatar
- PUT `/profile/language` (auth) – update language
- GET `/profile/stats` (auth) – metrics
- POST `/profile/deactivate` (auth) – deactivate
- GET `/profile/promotions` (auth) – promotions

---

### Chatbot (AI)
- POST `/chatbot` – chat with AI (guest or auth; optional `X-Chat-Session`/`session_key`)
- GET `/chatbot/history` – conversation history (auth user or guest session)

---

### Chat Real-time (REST-based)
- POST `/chat/guest/session` – create guest chat session
- GET `/chat/guest/{sessionId}/history` – guest chat history
- POST `/chat/guest/{sessionId}/message` – guest send message
- POST `/chat/guest/{sessionId}/transfer-human` – transfer to staff
- GET `/chat/guest/{sessionId}/messages` – poll new messages (last_message_id)
- GET `/chat/sessions/{id}/messages` (auth) – staff fetch messages
- POST `/chat/sessions/{id}/staff-message` (auth) – staff send message
- GET `/chat/staff/sessions` (auth) – staff assigned sessions

---

### Auth & Middleware Notes
- Global: locale middleware, `throttle:api`
- v1 group: `throttle:60,1`
- Protected blocks use `auth:sanctum`
- OTP routes use `throttle:otp`

---

### Conventions
- Responses use standard envelope with `trace_id` and `timestamp`.
- Pagination fields in `meta`: page, page_size, total_count, total_pages, has_next_page, has_previous_page.
- Caching applied to read endpoints with short TTLs; admin/mutations are uncached.


