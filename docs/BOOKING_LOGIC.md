## Booking Logic Specification (Aligned with Current Source + Extensions)

This document focuses strictly on booking (no payment). It documents what is implemented today and outlines near-term extensions. It is aligned with the current source code to avoid gaps.

### A. Data Model (as implemented)
- Booking
  - Keys: `id`, `booking_code`
  - Ownership: `user_id` (nullable for guests); `guest_name/email/phone`
  - Context: `branch_id`, `service_id`, `staff_id` (nullable)
  - Time: `booking_date` (date), `booking_time` (HH:mm), `duration` (minutes)
  - Status: `pending | confirmed | in_progress | completed | cancelled | no_show`
  - Notes: `notes`, `admin_notes`
- Staff: `branch_id`, `is_active`, `specialization` (JSON of service ids), pivot `staff_services`
- Service: `duration` (minutes), `is_active`, relations to branches/staff
- Branch: `opening_hours` (JSON, not yet enforced), `is_active`
- BookingStatusHistory: `old_status`, `new_status`, `changed_by`, `notes`

### B. Access Control
- Logged-in: users can create and manage their own bookings.
- Guests: can create bookings; identification via `guest_email/guest_phone`.
- Policies: controller uses standard `authorize` checks for list/view/update/cancel.

### C. Slot and Availability (as implemented)
- Slot representation: pair of `booking_date` + `booking_time` and `duration` integer.
- Availability check: `BookingRepository::isTimeSlotAvailable(branch_id, date, time, staff_id?)`
  - Checks for existing bookings at the exact `booking_time` on the same date and branch.
  - If `staff_id` is provided, also scopes by staff.
  - Current limitation: does not consider `duration`, overlap windows, or buffers.

### D. Creation Flow (current)
Inputs (StoreBookingRequest):
- Required: `branch_id`, `service_id`, `booking_date` (>= today), `booking_time (HH:mm)`
- Optional: `staff_id`, `notes`, `promotion_code`
- Guest required-if: `guest_name`, `guest_email`, `guest_phone` when unauthenticated

Process:
1) If authenticated, service sets `user_id = Auth::id()`; guests keep `user_id = null`.
2) Repository creates booking with provided fields; no auto-assignment when `staff_id` is null.
3) Status defaults to `pending` (per migration); business can confirm later.

### E. Cancellation and Modification (current)
- `Booking::canBeCancelled()`: allowed if `status in [pending, confirmed]` and `booking_date > now()+2h`.
- `Booking::canBeModified()`: allowed if `status in [pending, confirmed]` and `booking_date > now()+4h`.
- Controller `cancel()` updates status, writes `BookingStatusHistory`.

### F. Staff Assignment
- Specific staff: supported by passing `staff_id`; no explicit skill check yet (can be enforced via `staff_services` existence in a future Request rule).
- Any staff: not yet implemented; `staff_id` can be null at creation but no runtime assignment occurs.

### G. Gaps and Planned Extensions (backward-compatible)
1) Duration-aware availability and overlap detection
   - Extend `isTimeSlotAvailable` to check interval overlap using `duration` instead of exact time equality.
   - Add optional cleanup buffer after service for realistic turnover.
2) Business hours and working hours enforcement
   - Enforce `Branch.opening_hours` and introduce `Staff.working_hours` and breaks.
3) Slot granularity and alignment
   - Define a 5–15 minute grid; validate `booking_time` aligns to it.
4) Any-staff assignment
   - If `staff_id` is null: pick a qualified, available staff (same branch, supports service, no conflicts) using a fair policy.
5) Lead time and advance window
   - Add service-level `min_lead_minutes` and `max_advance_days` constraints.
6) Availability search API
   - Return available slots (and alternatives when chosen slot is unavailable).
7) Reschedule with cutoff
   - Add reschedule endpoint that re-validates availability and enforces a cutoff window.

### H. Minimal, Correct Availability Rule (intermediate step)
Overlap detection for a staff member (closed-open intervals):
```
new_start = combine(booking_date, booking_time)
new_end = new_start + duration
for each existing booking of the same staff on date:
  exist_start = combine(existing.booking_date, existing.booking_time)
  exist_end = exist_start + existing.duration
  if new_start < exist_end and exist_start < new_end:
    conflict
```
Branch-level conflicts can be handled similarly if branch capacity is limited.

### I. Recommended Request Validation Add-ons (safe to add now)
- Ensure `staff_id` belongs to the same `branch_id` (exists:staff,id where branch_id matches).
- Ensure `staff_id` is qualified for `service_id` (exists row in `staff_services`).
- Optional rule to align `booking_time` to a configured slot grid.

### J. Guest vs Logged-in Behavior
- Logged-in: `user_id` auto-populated; contact fields optional.
- Guest: must provide `guest_name`, `guest_email`, `guest_phone` for later lookup and verification.

#### Guest (Non-member) Booking with Email Verification
- Purpose: ensure guest identity via email OTP prior to finalizing booking.
- Data support: table `otp_verifications` exists (email/phone, otp, purpose, expires_at, verified_at).

Flow:
1) Initiate (client submits booking details + guest_email/phone)
   - System creates an OTP entry: `type=email`, `purpose=phone_verification` or `registration` (configurable), `expires_at = now()+5..10m`.
   - System emails OTP to `guest_email` (SMS for phone optional).
   - Booking is not persisted yet (recommended) or saved as `pending` with a short TTL (alternative).
2) Verify
   - Client posts `email` + `otp`.
   - On success: mark `otp_verifications.verified_at` and proceed.
3) Create booking
   - Persist booking with `user_id = null`, set `guest_*` fields.
   - Run availability checks as per sections C/D/H.
   - On success: status `confirmed` (or `pending` if business requires manual confirm), write `BookingStatusHistory`.
   - On failure: return alternatives (see section G6) or error.

Notes:
- For security, rate-limit OTP requests and verification attempts.
- Reuse a verified OTP within a short window to avoid re-sending on minor input corrections.
- If booking is pre-created before verification, do not expose it until OTP verifies; auto-cancel stale, unverified bookings via a cleanup job.

#### Guest Booking History Retrieval (Email + Birth Date)
- Goal: allow non-members to view their booking history securely.
- Inputs: `email` (required), `birth_date` (YYYY-MM-DD, optional but recommended), `otp` (required to finalize).

Flow:
1) Initiate lookup
   - Client submits `email` and `birth_date` (if known).
   - System issues OTP to the provided `email` via `otp_verifications` (purpose: `registration` or `phone_verification`).
2) Verify email
   - Client posts `email` + `otp` to verify.
3) Return bookings
   - Query criteria:
     - Primary: `bookings.guest_email = email` OR `users.email = email` (for member bookings)
     - If `birth_date` provided and user exists with that email: also require `users.date_of_birth = birth_date` to include member-owned bookings.
     - For pure guest bookings (no user), `birth_date` may not exist; rely on email OTP as the primary verifier.
   - Response: list of bookings with minimal fields (code, date/time, branch, service, staff, status).

Security notes:
- Email OTP verification is mandatory before returning any data.
- If a user account exists for the email, and `birth_date` is provided but mismatches, exclude member bookings and only return guest bookings tied to the email.
- Apply rate limiting and result size caps.

### K. Out of Scope
- Payments and refunds
- Pricing policies beyond persisted `service_price/discount/total_amount`
- Notification content and scheduling (email/SMS/push)


