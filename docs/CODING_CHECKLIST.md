# 📋 Beauty Clinic - Checklist Chức Năng Code

Checklist đầy đủ để không bị miss khi coding Backend cho Beauty Clinic System.

---

## 📍 Table of Contents
1. [Authentication & Authorization](#1-authentication--authorization)
2. [User Management](#2-user-management)
3. [Services Management](#3-services-management)
4. [Branches Management](#4-branches-management)
5. [Booking System](#5-booking-system)
6. [Payment Processing](#6-payment-processing)
7. [Review System](#7-review-system)
8. [Blog/Content Management](#8-blogcontent-management)
9. [Chatbot System](#9-chatbot-system)
10. [Contact & Support](#10-contact--support)
11. [Promotions & Discounts](#11-promotions--discounts)
12. [Admin Dashboard](#12-admin-dashboard)
13. [API Features](#13-api-features)
14. [Error Handling & Logging](#14-error-handling--logging)
15. [Security Features](#15-security-features)
16. [Performance & Optimization](#16-performance--optimization)

---

## 1. Authentication & Authorization

### Registration
- [ ] **POST** `/api/v1/auth/register`
  - [ ] Validate email format
  - [ ] Validate password strength (min 8 chars)
  - [ ] Validate password confirmation match
  - [ ] Hash password before saving
  - [ ] Generate unique user ID
  - [ ] Save language preference (vi/en/ja/zh)
  - [ ] Send welcome email
  - [ ] Return user + token
  - [ ] Handle duplicate email error
  - [ ] Generate avatar placeholder

### Login
- [ ] **POST** `/api/v1/auth/login`
  - [ ] Validate email/password
  - [ ] Check user exists
  - [ ] Check user is active
  - [ ] Verify password
  - [ ] Generate Sanctum token
  - [ ] Save device name
  - [ ] Update last_login_at
  - [ ] Log login activity
  - [ ] Handle invalid credentials (401)
  - [ ] Handle inactive account (403)

### Logout
- [ ] **POST** `/api/v1/auth/logout`
  - [ ] Revoke current token
  - [ ] Return success response
  - [ ] Handle unauthenticated user

### Get Current User
- [ ] **GET** `/api/v1/auth/me`
  - [ ] Return authenticated user data
  - [ ] Include avatar URL
  - [ ] Include stats (total bookings, total spent)
  - [ ] Handle missing token (401)

### Forgot Password
- [ ] **POST** `/api/v1/auth/forgot-password`
  - [ ] Validate email exists
  - [ ] Generate reset token
  - [ ] Send reset link email
  - [ ] Save reset token to database
  - [ ] Handle rate limiting
  - [ ] Return generic success (security)

### Reset Password
- [ ] **POST** `/api/v1/auth/reset-password`
  - [ ] Validate token validity
  - [ ] Check token not expired
  - [ ] Validate new password
  - [ ] Update password
  - [ ] Revoke token after use
  - [ ] Handle invalid/expired token

### Refresh Token
- [ ] Handle token refresh logic
- [ ] Update token expiration time

### Role-based Access
- [ ] Check admin middleware
- [ ] Check active user status
- [ ] Check email/phone verification status

---

## 2. User Management

### Profile
- [ ] **GET** `/api/v1/profile`
  - [ ] Return user profile
  - [ ] Include statistics
  - [ ] Include recent bookings
  - [ ] Include total spent
  - [ ] Include promotion count

- [ ] **PUT** `/api/v1/profile`
  - [ ] Validate input data
  - [ ] Update user info
  - [ ] Handle avatar upload
  - [ ] Return updated profile
  - [ ] Save change history

### Change Password
- [ ] **PUT** `/api/v1/profile/password`
  - [ ] Verify current password
  - [ ] Validate new password
  - [ ] Hash new password
  - [ ] Update password
  - [ ] Revoke all other sessions
  - [ ] Handle wrong current password

### Update Avatar
- [ ] **POST** `/api/v1/profile/avatar`
  - [ ] Validate image file
  - [ ] Check file size (max 2MB)
  - [ ] Check file type (jpg/png/webp)
  - [ ] Resize image (e.g. 300x300)
  - [ ] Upload to storage
  - [ ] Delete old avatar
  - [ ] Update user record

- [ ] **DELETE** `/api/v1/profile/avatar`
  - [ ] Delete avatar from storage
  - [ ] Set avatar to null
  - [ ] Return success

### Profile Stats
- [ ] **GET** `/api/v1/profile/stats`
  - [ ] Total bookings count
  - [ ] Total amount spent
  - [ ] Favorite service
  - [ ] Most used branch
  - [ ] Average rating given
  - [ ] Member since date

### Deactivate Account
- [ ] **POST** `/api/v1/profile/deactivate`
  - [ ] Set is_active to false
  - [ ] Revoke all tokens
  - [ ] Cancel pending bookings
  - [ ] Send deactivation email
  - [ ] Soft delete approach

### Profile Promotions
- [ ] **GET** `/api/v1/profile/promotions`
  - [ ] Get user's available promotions
  - [ ] Check promotion validity
  - [ ] Filter expired promotions
  - [ ] Include remaining uses
  - [ ] Include terms and conditions

---

## 3. Services Management

### List Services
- [ ] **GET** `/api/v1/services`
  - [ ] Pagination (page, per_page)
  - [ ] Filter by category
  - [ ] Filter by featured
  - [ ] Filter by price range
  - [ ] Search by name
  - [ ] Sort (price, name, created_at)
  - [ ] Localization (locale param)
  - [ ] Only show active services
  - [ ] Calculate ratings from reviews
  - [ ] Include image URLs
  - [ ] Meta pagination info

### Service Detail
- [ ] **GET** `/api/v1/services/{id}`
  - [ ] Get by ID or slug
  - [ ] Include category info
  - [ ] Include available branches
  - [ ] Include related services
  - [ ] Include reviews summary
  - [ ] Include gallery images
  - [ ] Increment views_count
  - [ ] Check service is active
  - [ ] Handle not found (404)

### Create Service (Admin)
- [ ] **POST** `/api/v1/services`
  - [ ] Validate all fields
  - [ ] Check admin permission
  - [ ] Generate slug from name
  - [ ] Handle multi-language fields
  - [ ] Upload service image
  - [ ] Handle gallery upload
  - [ ] Set is_active = true by default
  - [ ] Set display_order
  - [ ] Save SEO meta fields
  - [ ] Return created service

### Update Service (Admin)
- [ ] **PUT** `/api/v1/services/{id}`
  - [ ] Update service info
  - [ ] Handle image updates
  - [ ] Update slug if name changed
  - [ ] Preserve rating/reviews data
  - [ ] Return updated service

### Delete Service (Admin)
- [ ] **DELETE** `/api/v1/services/{id}`
  - [ ] Soft delete (set is_active = false)
  - [ ] Don't delete if has bookings
  - [ ] Archive related data
  - [ ] Return success response

### Service Categories
- [ ] **GET** `/api/v1/service-categories`
  - [ ] List all active categories
  - [ ] Include service count
  - [ ] Sort by display_order
  - [ ] Localization support

---

## 4. Branches Management

### List Branches
- [ ] **GET** `/api/v1/branches`
  - [ ] Pagination
  - [ ] Localization
  - [ ] Calculate distance if lat/long provided
  - [ ] Sort by distance
  - [ ] Only show active branches
  - [ ] Include opening hours
  - [ ] Include amenities
  - [ ] Include rating/reviews

### Branch Detail
- [ ] **GET** `/api/v1/branches/{id}`
  - [ ] Full branch info
  - [ ] Include available services
  - [ ] Include staff list
  - [ ] Include images gallery
  - [ ] Include opening hours
  - [ ] Include amenities
  - [ ] Include location (lat/long)
  - [ ] Handle not found

### Available Time Slots
- [ ] **GET** `/api/v1/branches/{id}/available-slots`
  - [ ] Require date + service_id
  - [ ] Get staff available for service
  - [ ] Get existing bookings for date
  - [ ] Calculate available slots (30-min intervals)
  - [ ] Check working hours
  - [ ] Exclude booked slots
  - [ ] Show available staff for each slot
  - [ ] Handle past dates (error)

---

## 5. Booking System

### Create Booking
- [ ] **POST** `/api/v1/bookings`
  - [ ] **Authentication: Optional (Guest or Member)**
  - [ ] Validate guest info if not authenticated
  - [ ] Validate booking_date (not past)
  - [ ] Validate booking_time format
  - [ ] Check slot availability
  - [ ] Check service exists and active
  - [ ] Check branch exists and active
  - [ ] Check staff availability
  - [ ] Check booking is within business hours
  - [ ] Validate promotion code (if provided)
  - [ ] Calculate pricing
  - [ ] Apply discount
  - [ ] Generate booking code
  - [ ] Set status = 'pending'
  - [ ] Set payment_status = 'pending'
  - [ ] Save booking
  - [ ] Create status history entry
  - [ ] Send confirmation email
  - [ ] Return booking with code
  - [ ] Handle slot conflict
  - [ ] Handle invalid promotion

### Get Booking Detail
- [ ] **GET** `/api/v1/bookings/{id}`
  - [ ] Check ownership or admin
  - [ ] Include branch info
  - [ ] Include service info
  - [ ] Include staff info
  - [ ] Include customer info
  - [ ] Include payment info
  - [ ] Include status history
  - [ ] Handle not found
  - [ ] Handle unauthorized access

### List Bookings (User)
- [ ] **GET** `/api/v1/my-bookings`
  - [ ] Filter by user_id
  - [ ] Optional status filter
  - [ ] Pagination
  - [ ] Sort by date (newest first)
  - [ ] Include service summary
  - [ ] Include branch summary

### List Bookings (Admin)
- [ ] **GET** `/api/v1/bookings`
  - [ ] Admin only
  - [ ] Filter by branch
  - [ ] Filter by service
  - [ ] Filter by status
  - [ ] Filter by date range
  - [ ] Search by booking code
  - [ ] Pagination
  - [ ] Export capability

### Update Booking
- [ ] **PUT** `/api/v1/bookings/{id}`
  - [ ] Check ownership
  - [ ] Validate can_be_modified (min 4 hours before)
  - [ ] Check new slot availability
  - [ ] Recalculate pricing if date/time changed
  - [ ] Update booking
  - [ ] Create status history
  - [ ] Send update email
  - [ ] Handle invalid status

### Cancel Booking
- [ ] **POST** `/api/v1/bookings/{id}/cancel`
  - [ ] Check ownership
  - [ ] Check can_be_cancelled (min 2 hours before)
  - [ ] Validate cancellation_reason
  - [ ] Update status to 'cancelled'
  - [ ] Set cancelled_at
  - [ ] Create status history
  - [ ] Process refund if paid
  - [ ] Send cancellation email
  - [ ] Free up the time slot
  - [ ] Handle already cancelled

### Booking Status Flow
- [ ] **Statuses:** pending → confirmed → completed
- [ ] **Cancellation:** pending/confirmed → cancelled
- [ ] **Auto-confirmation:** pending → confirmed (after payment)
- [ ] **Auto-completion:** confirmed → completed (after service)
- [ ] Handle each status transition
- [ ] Track in status history

### Admin Confirm Booking
- [ ] Admin can manually confirm
- [ ] Admin can reject booking
- [ ] Admin can update status

---

## 6. Payment Processing

### Create Payment Intent
- [ ] **POST** `/api/v1/payments/create-intent`
  - [ ] Validate booking exists
  - [ ] Check booking belongs to user
  - [ ] Check payment_status = 'pending'
  - [ ] Create Stripe payment intent
  - [ ] Store payment intent ID
  - [ ] Return client_secret
  - [ ] Set currency = VND
  - [ ] Handle error from Stripe

### Confirm Payment
- [ ] **POST** `/api/v1/payments/confirm`
  - [ ] Validate payment_intent_id
  - [ ] Check payment on Stripe
  - [ ] Create payment record
  - [ ] Generate payment code
  - [ ] Update booking payment_status = 'paid'
  - [ ] Confirm booking status
  - [ ] Update promotion usage count
  - [ ] Send payment receipt email
  - [ ] Log payment activity
  - [ ] Handle failed payment
  - [ ] Handle duplicate payment

### Payment Webhook
- [ ] **POST** `/api/v1/payments/webhook`
  - [ ] Verify Stripe signature
  - [ ] Handle payment_intent.succeeded
  - [ ] Handle payment_intent.payment_failed
  - [ ] Update booking status
  - [ ] Send confirmation email
  - [ ] Handle other webhook events
  - [ ] Return 200 always

### Refund
- [ ] Process refund for cancelled bookings
- [ ] Create refund record
- [ ] Update booking status
- [ ] Send refund email

---

## 7. Review System

### List Reviews
- [ ] **GET** `/api/v1/reviews`
  - [ ] Filter by service_id
  - [ ] Filter by rating (1-5)
  - [ ] Filter by staff_id
  - [ ] Filter by branch_id
  - [ ] Only show approved reviews
  - [ ] Pagination
  - [ ] Sort by recent/helpful/rating
  - [ ] Include user info (anonymized)
  - [ ] Include service info
  - [ ] Include admin responses

### Create Review
- [ ] **POST** `/api/v1/reviews`
  - [ ] Check user completed booking
  - [ ] Check not already reviewed
  - [ ] Validate rating (1-5)
  - [ ] Validate all sub-ratings
  - [ ] Handle image uploads (max 5)
  - [ ] Resize images
  - [ ] Set is_approved = false (pending)
  - [ ] Save review
  - [ ] Update booking reviewed status
  - [ ] Send for admin approval
  - [ ] Auto-approve after 24h if no admin action

### Admin Review Management
- [ ] Approve review
- [ ] Reject review
- [ ] Add admin response
- [ ] Feature review
- [ ] Update service rating
- [ ] Update staff rating

---

## 8. Blog/Content Management

### List Posts
- [ ] **GET** `/api/v1/posts`
  - [ ] Only published posts
  - [ ] Filter by category
  - [ ] Search by title/content
  - [ ] Pagination
  - [ ] Sort by published date
  - [ ] Localization
  - [ ] Include featured image
  - [ ] Include author info
  - [ ] Include reading time

### Featured Posts
- [ ] **GET** `/api/v1/posts/featured`
  - [ ] Get featured posts only
  - [ ] Limit to top 5
  - [ ] Sort by featured order

### Post Detail
- [ ] **GET** `/api/v1/posts/{slug}`
  - [ ] Get by slug
  - [ ] Increment views_count
  - [ ] Include full content
  - [ ] Include images gallery
  - [ ] Include author details
  - [ ] Include related posts
  - [ ] Include tags
  - [ ] Include SEO meta

### Admin Create/Update Post
- [ ] Create blog post
- [ ] Update blog post
- [ ] Delete post
- [ ] Publish/unpublish
- [ ] Upload featured image
- [ ] Upload gallery images
- [ ] Handle categories
- [ ] Handle tags

---

## 9. Chatbot System

### Send Message
- [ ] **POST** `/api/v1/chatbot/message`
  - [ ] Create or get session
  - [ ] Process user message
  - [ ] Generate bot response (intent detection)
  - [ ] Handle common queries
  - [ ] Provide relevant suggestions
  - [ ] Save to database
  - [ ] Return response + suggestions

### Get Sessions
- [ ] **GET** `/api/v1/chatbot/sessions`
  - [ ] List user's sessions
  - [ ] Include recent messages
  - [ ] Show session status
  - [ ] Filter active sessions

### Get Session Detail
- [ ] **GET** `/api/v1/chatbot/sessions/{id}`
  - [ ] Get full message history
  - [ ] Return chronologically
  - [ ] Include metadata

### Delete Session
- [ ] **DELETE** `/api/v1/chatbot/sessions/{id}`
  - [ ] Archive session
  - [ ] Delete messages

### Clear Messages
- [ ] **DELETE** `/api/v1/chatbot/sessions/{id}/messages`
  - [ ] Clear message history
  - [ ] Keep session active

---

## 10. Contact & Support

### Submit Contact Form
- [ ] **POST** `/api/v1/contact`
  - [ ] Validate name, email, phone, message
  - [ ] Save contact submission
  - [ ] Generate reference code
  - [ ] Set status = 'new'
  - [ ] Send auto-reply email
  - [ ] Notify admin
  - [ ] Store IP address
  - [ ] Store user agent
  - [ ] Rate limiting (max 5/hour)

### Admin Contact Management
- [ ] List submissions
- [ ] Filter by status
- [ ] Mark as in-progress
- [ ] Add response
- [ ] Mark as resolved
- [ ] Close submission

---

## 11. Promotions & Discounts

### Apply Promotion to Booking
- [ ] Validate promotion code
- [ ] Check promotion validity dates
- [ ] Check promotion is active
- [ ] Check min_amount requirement
- [ ] Check max_uses limit
- [ ] Check max_uses_per_user limit
- [ ] Check applicable_to (all/services)
- [ ] Calculate discount amount
- [ ] Apply to booking total
- [ ] Create promotion usage record
- [ ] Increment used_count

### List Available Promotions (User)
- [ ] Get user's promotions
- [ ] Filter valid promotions only
- [ ] Check usage limits
- [ ] Include terms
- [ ] Include remaining uses

### Admin Promotion Management
- [ ] Create promotion
- [ ] Update promotion
- [ ] Set discount type (percentage/fixed)
- [ ] Set valid from/to dates
- [ ] Set usage limits
- [ ] Set applicable services
- [ ] Activate/deactivate

---

## 12. Admin Dashboard

### User Management (Admin)
- [ ] **GET** `/api/v1/users` - List users
  - [ ] Pagination
  - [ ] Search by name/email
  - [ ] Filter by status (active/inactive)
  - [ ] Filter by role (user/admin)
  - [ ] Include stats (total bookings, spent)

- [ ] **GET** `/api/v1/users/{id}` - Get user detail
  - [ ] Full user info
  - [ ] Booking history
  - [ ] Review history
  - [ ] Payment history

- [ ] **PUT** `/api/v1/users/{id}` - Update user
  - [ ] Update user info
  - [ ] Activate/deactivate
  - [ ] Change role

- [ ] **DELETE** `/api/v1/users/{id}` - Delete user
  - [ ] Soft delete
  - [ ] Archive data

### Dashboard Statistics
- [ ] Total users count
- [ ] Total bookings today/week/month
- [ ] Revenue today/week/month
- [ ] Top services
- [ ] Top branches
- [ ] Recent bookings
- [ ] Pending reviews
- [ ] Contact submissions

### Reports
- [ ] Sales report by date range
- [ ] Booking report by branch
- [ ] Service popularity report
- [ ] User activity report
- [ ] Revenue by service
- [ ] Export to Excel/PDF

---

## 13. API Features

### Response Format
- [ ] Standard response wrapper
  - [ ] success (boolean)
  - [ ] message (string)
  - [ ] data (mixed)
  - [ ] error (object or null)
  - [ ] meta (pagination info)
  - [ ] trace_id (string)
  - [ ] timestamp (ISO 8601)

### Error Responses
- [ ] 400 Bad Request
- [ ] 401 Unauthorized
- [ ] 403 Forbidden
- [ ] 404 Not Found
- [ ] 422 Validation Error
- [ ] 429 Too Many Requests
- [ ] 500 Internal Server Error

### Pagination
- [ ] page, page_size params
- [ ] total_count
- [ ] total_pages
- [ ] has_next_page
- [ ] has_previous_page
- [ ] per_page max 100

### Localization
- [ ] locale query param
- [ ] Support vi, en, ja, zh
- [ ] Return localized content
- [ ] Default locale = 'vi'

### Rate Limiting
- [ ] Guest: 60 req/min
- [ ] Authenticated: 100 req/min
- [ ] Admin: 200 req/min
- [ ] Return X-RateLimit-* headers

### CORS
- [ ] Configure allowed origins
- [ ] Allow credentials
- [ ] Allow methods (GET, POST, PUT, DELETE)
- [ ] Allow headers

---

## 14. Error Handling & Logging

### Exception Handling
- [ ] Global exception handler
- [ ] Custom business exceptions
- [ ] Resource not found exception
- [ ] Validation exception
- [ ] Format error responses
- [ ] Log errors to file

### Activity Logging
- [ ] Log user actions
- [ ] Log API requests/responses
- [ ] Log errors with stack trace
- [ ] Log payment transactions
- [ ] Log booking changes
- [ ] Structured JSON logging
- [ ] Daily log rotation

### Sentry Integration
- [ ] Send errors to Sentry
- [ ] Include context
- [ ] Filter sensitive data

---

## 15. Security Features

### Authentication
- [ ] Laravel Sanctum tokens
- [ ] Token expiration (1 hour default)
- [ ] Token refresh mechanism
- [ ] Revoke all tokens on password change

### Authorization
- [ ] Check ownership for bookings
- [ ] Admin middleware
- [ ] Activity logging for sensitive actions
- [ ] Permission checks

### Input Validation
- [ ] Validate all inputs
- [ ] Sanitize user inputs
- [ ] XSS prevention
- [ ] SQL injection prevention
- [ ] CSRF protection (web routes)

### File Uploads
- [ ] Validate file types
- [ ] Validate file size (max 2MB for images)
- [ ] Rename files (security)
- [ ] Virus scanning (optional)
- [ ] Store in protected location

### API Security
- [ ] Rate limiting per endpoint
- [ ] IP whitelisting for admin routes (optional)
- [ ] API key for external services
- [ ] Encrypt sensitive data
- [ ] Secure password hashing

---

## 16. Performance & Optimization

### Database
- [ ] Add indexes on foreign keys
- [ ] Add indexes on frequently queried fields
- [ ] Add indexes on status fields
- [ ] Optimize N+1 queries (eager loading)
- [ ] Use database transactions
- [ ] Add composite indexes

### Caching
- [ ] Cache service lists
- [ ] Cache branch lists
- [ ] Cache categories
- [ ] Cache featured posts
- [ ] Clear cache on updates
- [ ] Redis for session storage

### Image Optimization
- [ ] Resize images on upload
- [ ] Create thumbnails
- [ ] Optimize image quality
- [ ] Lazy loading support
- [ ] CDN integration (optional)

### Query Optimization
- [ ] Use select() to limit fields
- [ ] Use pagination for large lists
- [ ] Limit eager loading
- [ ] Use database indexes
- [ ] Monitor slow queries

### API Response
- [ ] Minimize response size
- [ ] Compress responses (gzip)
- [ ] Cache static resources
- [ ] Use HTTP/2

---

## Testing Checklist

### Unit Tests
- [ ] AuthService tests
- [ ] BookingService tests
- [ ] PaymentService tests
- [ ] ReviewService tests
- [ ] Model tests
- [ ] Service tests

### Feature Tests
- [ ] Authentication flow
- [ ] Booking creation
- [ ] Payment processing
- [ ] Review submission
- [ ] Admin operations

### Integration Tests
- [ ] Full booking flow
- [ ] Payment flow
- [ ] Email sending
- [ ] File upload

---

## Additional Features to Consider

### Email Notifications
- [ ] Welcome email
- [ ] Booking confirmation
- [ ] Booking reminder (24h before)
- [ ] Booking cancellation
- [ ] Payment receipt
- [ ] Password reset
- [ ] Review approval

### SMS Notifications (Future)
- [ ] Booking confirmation SMS
- [ ] Booking reminder SMS
- [ ] SMS OTP verification

### Push Notifications (Future)
- [ ] Booking updates
- [ ] Promotions
- [ ] Reminders

### Multi-language Support
- [ ] Vietnamese (vi) - Primary
- [ ] English (en)
- [ ] Japanese (ja)
- [ ] Chinese (zh)
- [ ] Store translations in JSON
- [ ] Return based on locale param

### Reporting & Analytics
- [ ] Track conversion rates
- [ ] Track popular services
- [ ] Track peak booking times
- [ ] Track user behavior
- [ ] Revenue analytics

---

## Deployment Checklist

### Before Deployment
- [ ] Run all tests
- [ ] Check environment variables
- [ ] Update database migrations
- [ ] Seed database with initial data
- [ ] Generate application key
- [ ] Optimize assets
- [ ] Check file permissions

### Production Setup
- [ ] Configure .env for production
- [ ] Set APP_DEBUG=false
- [ ] Configure database
- [ ] Configure email service
- [ ] Configure Stripe keys
- [ ] Set up SSL certificate
- [ ] Configure queue workers
- [ ] Set up cron jobs
- [ ] Enable logging
- [ ] Set up backups

### Monitoring
- [ ] Application monitoring (Laravel Telescope/Log)
- [ ] Error tracking (Sentry)
- [ ] Performance monitoring
- [ ] Uptime monitoring
- [ ] Database monitoring
- [ ] Queue monitoring

---

## Notes

- Use Laravel best practices
- Follow PSR standards
- Write clean, maintainable code
- Add comments for complex logic
- Use type hints
- Handle edge cases
- Test thoroughly
- Document APIs with OpenAPI/Swagger

**Last Updated:** 2025-10-27
**Version:** 1.0

