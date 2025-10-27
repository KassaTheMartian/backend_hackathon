# Kiến Trúc Hệ Thống - Sơ Đồ Tổng Quan

## 1. Kiến Trúc Tổng Thể (High-Level Architecture)

```
┌───────────────────────────────────────────────────────────────────────────┐
│                              PRESENTATION LAYER                            │
├───────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│  ┌────────────────────┐  ┌────────────────────┐  ┌────────────────────┐  │
│  │   Customer Web     │  │   Admin Panel      │  │   Mobile Browser   │  │
│  │   (Next.js 14+)    │  │  (Laravel Blade)   │  │   (Responsive)     │  │
│  │                    │  │  + Tailwind CSS    │  │                    │  │
│  │  • Homepage        │  │  • Dashboard       │  │  • PWA Support     │  │
│  │  • Services        │  │  • Bookings Mgmt   │  │  • Touch Optimized │  │
│  │  • Booking         │  │  • Customer Mgmt   │  │  • Offline Mode    │  │
│  │  • Profile         │  │  • Analytics       │  │                    │  │
│  │  • Blog            │  │  • Settings        │  │                    │  │
│  └────────────────────┘  └────────────────────┘  └────────────────────┘  │
│           │                        │                        │             │
└───────────┼────────────────────────┼────────────────────────┼─────────────┘
            │                        │                        │
            │    HTTPS/REST API      │    HTTPS/Sessions     │
            │                        │                        │
            ▼                        ▼                        ▼
┌───────────────────────────────────────────────────────────────────────────┐
│                          API GATEWAY / LOAD BALANCER                       │
│                               (Nginx / HAProxy)                            │
└───────────────────────────────────────────────────────────────────────────┘
            │                                                │
            ▼                                                ▼
┌───────────────────────────────────────────────────────────────────────────┐
│                           APPLICATION LAYER                                │
├───────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│  ┌─────────────────────────────────────────────────────────────────────┐  │
│  │                     Laravel 11+ API Backend                         │  │
│  │                                                                     │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────────┐ │  │
│  │  │ Controllers  │  │  Services    │  │    Repositories          │ │  │
│  │  │              │  │              │  │                          │ │  │
│  │  │ • Auth       │→ │ • Booking    │→ │ • User Repository        │ │  │
│  │  │ • Services   │  │ • Payment    │  │ • Booking Repository     │ │  │
│  │  │ • Bookings   │  │ • Chatbot    │  │ • Service Repository     │ │  │
│  │  │ • Reviews    │  │ • Notification│ │ • Branch Repository      │ │  │
│  │  └──────────────┘  └──────────────┘  └──────────────────────────┘ │  │
│  │                                                                     │  │
│  │  ┌──────────────────────────────────────────────────────────────┐ │  │
│  │  │                    Background Jobs Queue                      │ │  │
│  │  │                     (Laravel Horizon)                         │ │  │
│  │  │                                                                │ │  │
│  │  │  • SendBookingConfirmation     • ProcessPayment              │ │  │
│  │  │  • SendReminderEmail           • GenerateInvoice             │ │  │
│  │  │  • SendSMSNotification         • UpdateServiceStats          │ │  │
│  │  └──────────────────────────────────────────────────────────────┘ │  │
│  └─────────────────────────────────────────────────────────────────────┘  │
│                                                                            │
└───────────────────────────────────────────────────────────────────────────┘
            │                                                │
            ▼                                                ▼
┌───────────────────────────────────────────────────────────────────────────┐
│                         INTEGRATION LAYER                                  │
├───────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Stripe     │  │   OpenAI     │  │    Twilio    │  │    Email     │  │
│  │   Payment    │  │   Chatbot    │  │     SMS      │  │   Service    │  │
│  │              │  │              │  │              │  │              │  │
│  │ • Create PI  │  │ • GPT-4 API  │  │ • Send SMS   │  │ • SendGrid   │  │
│  │ • Confirm    │  │ • Embeddings │  │ • Verify OTP │  │ • SES        │  │
│  │ • Refund     │  │ • Fine-tune  │  │              │  │ • Mailtrap   │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘  │
│                                                                            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Google Maps  │  │   Firebase   │  │     AWS S3   │  │  CloudFlare  │  │
│  │     API      │  │     FCM      │  │ /DO Spaces   │  │     CDN      │  │
│  │              │  │              │  │              │  │              │  │
│  │ • Geocoding  │  │ • Push       │  │ • Images     │  │ • Assets     │  │
│  │ • Distance   │  │ • Analytics  │  │ • Documents  │  │ • Images     │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘  │
│                                                                            │
└───────────────────────────────────────────────────────────────────────────┘
            │                                                │
            ▼                                                ▼
┌───────────────────────────────────────────────────────────────────────────┐
│                            DATA LAYER                                      │
├───────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│  ┌─────────────────────────────────┐  ┌─────────────────────────────────┐│
│  │       MySQL 8.0+ Cluster        │  │      Redis Cluster              ││
│  │                                 │  │                                 ││
│  │  ┌─────────┐      ┌──────────┐ │  │  ┌──────────────────────────┐  ││
│  │  │ Master  │─────>│ Slave 1  │ │  │  │   Cache Layer            │  ││
│  │  │  (Write)│      │  (Read)  │ │  │  │   • API Responses        │  ││
│  │  └─────────┘      └──────────┘ │  │  │   • Session Data         │  ││
│  │       │           ┌──────────┐ │  │  │   • Query Results        │  ││
│  │       └──────────>│ Slave 2  │ │  │  └──────────────────────────┘  ││
│  │                   │  (Read)  │ │  │                                 ││
│  │                   └──────────┘ │  │  ┌──────────────────────────┐  ││
│  │                                 │  │  │   Queue Backend          │  ││
│  │  • users                        │  │  │   • Job Queue            │  ││
│  │  • services                     │  │  │   • Failed Jobs          │  ││
│  │  • bookings                     │  │  └──────────────────────────┘  ││
│  │  • payments                     │  │                                 ││
│  │  • reviews                      │  │  ┌──────────────────────────┐  ││
│  │  • posts                        │  │  │   Pub/Sub                │  ││
│  │  • ... all tables               │  │  │   • Real-time Chat       │  ││
│  │                                 │  │  │   • Notifications        │  ││
│  └─────────────────────────────────┘  └─────────────────────────────────┘│
│                                                                            │
└───────────────────────────────────────────────────────────────────────────┘
            │                                                │
            ▼                                                ▼
┌───────────────────────────────────────────────────────────────────────────┐
│                        MONITORING & LOGGING                                │
├───────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Sentry     │  │   Horizon    │  │  New Relic   │  │   ELK Stack  │  │
│  │              │  │              │  │              │  │              │  │
│  │ • Errors     │  │ • Queue      │  │ • APM        │  │ • Logs       │  │
│  │ • Exceptions │  │ • Monitoring │  │ • Metrics    │  │ • Search     │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘  │
│                                                                            │
└───────────────────────────────────────────────────────────────────────────┘
```

## 2. Data Flow Diagrams

### 2.1 Booking Flow - Guest User

```
┌─────────────┐
│   Guest     │
│   User      │
└──────┬──────┘
       │
       │ 1. Browse Services
       ▼
┌─────────────────┐
│  Services API   │
│  GET /services  │
└────────┬────────┘
         │
         │ 2. Return services list
         ▼
┌─────────────────┐
│  Select Service │
│  Select Branch  │
│  Select Date    │
└────────┬────────┘
         │
         │ 3. Check available slots
         ▼
┌──────────────────────────────┐
│ GET /branches/{id}/           │
│     available-slots           │
│                               │
│ ┌──────────────────────────┐ │
│ │ BookingService           │ │
│ │ • Query existing bookings│ │
│ │ • Calculate time slots   │ │
│ │ • Check staff availability│ │
│ └──────────────────────────┘ │
└────────┬─────────────────────┘
         │
         │ 4. Display available slots
         ▼
┌─────────────────┐
│  Select Time    │
│  Enter Info     │
│  (Guest)        │
└────────┬────────┘
         │
         │ 5. Submit booking
         │    POST /bookings
         ▼
┌──────────────────────────────┐
│ BookingController            │
│                               │
│ ┌──────────────────────────┐ │
│ │ Validation               │ │
│ │ • Check time conflict    │ │
│ │ • Validate input         │ │
│ └────────┬─────────────────┘ │
│          │                    │
│          ▼                    │
│ ┌──────────────────────────┐ │
│ │ Create Booking           │ │
│ │ • Generate booking code  │ │
│ │ • Calculate total        │ │
│ │ • Save to database       │ │
│ └────────┬─────────────────┘ │
│          │                    │
│          ▼                    │
│ ┌──────────────────────────┐ │
│ │ Dispatch Jobs            │ │
│ │ • SendConfirmationEmail  │ │
│ │ • SendSMS                │ │
│ └──────────────────────────┘ │
└────────┬─────────────────────┘
         │
         │ 6. Return booking confirmation
         ▼
┌─────────────────┐
│  Confirmation   │
│     Page        │
│                 │
│ • Booking Code  │
│ • Details       │
│ • Payment Link  │
└─────────────────┘
```

### 2.2 Payment Flow

```
┌─────────────┐
│   Customer  │
└──────┬──────┘
       │
       │ 1. Initiate payment
       │    POST /payments/create-intent
       ▼
┌──────────────────────────────┐
│ PaymentController            │
│                               │
│ ┌──────────────────────────┐ │
│ │ PaymentService           │ │
│ │                          │ │
│ │ new Stripe\StripeClient  │ │
│ │ createPaymentIntent()    │ │
│ └────────┬─────────────────┘ │
└──────────┼──────────────────┘
           │
           │ 2. Call Stripe API
           ▼
┌──────────────────────────────┐
│      Stripe Payment API       │
│                               │
│  • Create Payment Intent     │
│  • Return client_secret      │
└────────┬─────────────────────┘
         │
         │ 3. Return client_secret
         ▼
┌─────────────────┐
│   Frontend      │
│                 │
│ Stripe.js       │
│ confirmPayment()│
└────────┬────────┘
         │
         │ 4. User enters card info
         │    & confirms
         ▼
┌──────────────────────────────┐
│      Stripe Processing        │
└────────┬─────────────────────┘
         │
         │ 5. Webhook notification
         │    POST /payments/webhook
         ▼
┌──────────────────────────────┐
│ WebhookController            │
│                               │
│ ┌──────────────────────────┐ │
│ │ Verify signature         │ │
│ │ Parse event              │ │
│ └────────┬─────────────────┘ │
│          │                    │
│          ▼                    │
│ ┌──────────────────────────┐ │
│ │ Update Payment Status    │ │
│ │ Update Booking Status    │ │
│ │ Dispatch Jobs:           │ │
│ │ • SendReceipt            │ │
│ │ • GenerateInvoice        │ │
│ └──────────────────────────┘ │
└──────────────────────────────┘
```

### 2.3 Chatbot Flow

```
┌─────────────┐
│   User      │
└──────┬──────┘
       │
       │ 1. Send message
       │    POST /chatbot/message
       │    { message: "Tôi muốn đặt lịch hẹn" }
       ▼
┌──────────────────────────────┐
│ ChatbotController            │
│                               │
│ ┌──────────────────────────┐ │
│ │ Get/Create session       │ │
│ │ Load conversation history│ │
│ └────────┬─────────────────┘ │
│          │                    │
│          ▼                    │
│ ┌──────────────────────────┐ │
│ │ ChatbotService           │ │
│ │                          │ │
│ │ Build context:           │ │
│ │ • Previous messages      │ │
│ │ • User info (if logged) │ │
│ │ • Current page/state     │ │
│ └────────┬─────────────────┘ │
└──────────┼──────────────────┘
           │
           │ 2. Call OpenAI API
           ▼
┌──────────────────────────────┐
│      OpenAI GPT-4 API         │
│                               │
│  System: "You are a helpful  │
│  assistant for a beauty      │
│  clinic..."                   │
│                               │
│  User: "Tôi muốn đặt lịch"   │
│                               │
│  ┌──────────────────────────┐│
│  │ Intent Recognition       ││
│  │ • Booking inquiry        ││
│  │ Confidence: 0.95         ││
│  └──────────────────────────┘│
└────────┬─────────────────────┘
         │
         │ 3. Generate response
         ▼
┌──────────────────────────────┐
│ ChatbotService               │
│                               │
│ ┌──────────────────────────┐ │
│ │ Post-processing          │ │
│ │                          │ │
│ │ IF intent = booking:     │ │
│ │   • Get available services│ │
│ │   • Generate suggestions │ │
│ │   • Add quick action btns│ │
│ │                          │ │
│ │ Save message to DB       │ │
│ └──────────────────────────┘ │
└────────┬─────────────────────┘
         │
         │ 4. Return response
         ▼
┌─────────────────┐
│   Frontend      │
│   Chatbot UI    │
│                 │
│ • Display msg   │
│ • Show buttons: │
│   [Xem dịch vụ] │
│   [Đặt lịch]    │
└─────────────────┘
```

## 3. Security Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                      SECURITY LAYERS                            │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Layer 1: Network Security                                     │
│  ┌────────────────────────────────────────────────────────┐   │
│  │ • CloudFlare WAF (Web Application Firewall)            │   │
│  │ • DDoS Protection                                       │   │
│  │ • SSL/TLS Encryption (TLS 1.3)                         │   │
│  │ • Rate Limiting at CDN level                           │   │
│  └────────────────────────────────────────────────────────┘   │
│                           ▼                                     │
│  Layer 2: Application Gateway                                  │
│  ┌────────────────────────────────────────────────────────┐   │
│  │ • Nginx/HAProxy                                         │   │
│  │ • IP Whitelisting for admin                            │   │
│  │ • Request filtering                                     │   │
│  │ • CORS configuration                                    │   │
│  └────────────────────────────────────────────────────────┘   │
│                           ▼                                     │
│  Layer 3: Application Security                                 │
│  ┌────────────────────────────────────────────────────────┐   │
│  │ Authentication & Authorization:                         │   │
│  │ • Laravel Sanctum (API tokens)                         │   │
│  │ • JWT for admin panel                                   │   │
│  │ • Role-based access control (RBAC)                     │   │
│  │ • Permission-based authorization                        │   │
│  │                                                          │   │
│  │ Input Validation:                                       │   │
│  │ • Form Request validation                              │   │
│  │ • XSS protection                                        │   │
│  │ • SQL injection prevention (ORM)                       │   │
│  │ • CSRF tokens                                           │   │
│  │                                                          │   │
│  │ Rate Limiting:                                          │   │
│  │ • API throttling (60 req/min guest, 100 req/min user)  │   │
│  │ • Login attempt limiting                                │   │
│  │ • OTP request limiting                                  │   │
│  │                                                          │   │
│  │ Data Encryption:                                        │   │
│  │ • Password hashing (bcrypt)                            │   │
│  │ • Sensitive data encryption at rest                    │   │
│  │ • API payload encryption (optional)                    │   │
│  └────────────────────────────────────────────────────────┘   │
│                           ▼                                     │
│  Layer 4: Data Security                                        │
│  ┌────────────────────────────────────────────────────────┐   │
│  │ • Database user permissions (least privilege)          │   │
│  │ • Encrypted database connections                        │   │
│  │ • Regular backups (encrypted)                          │   │
│  │ • Audit logging                                         │   │
│  │ • PII data masking in logs                             │   │
│  └────────────────────────────────────────────────────────┘   │
│                                                                 │
└────────────────────────────────────────────────────────────────┘
```

## 4. Deployment Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                         PRODUCTION ENVIRONMENT                  │
└────────────────────────────────────────────────────────────────┘

                          ┌─────────────┐
                          │ CloudFlare  │
                          │     CDN     │
                          └──────┬──────┘
                                 │
                   ┌─────────────┼─────────────┐
                   │                           │
         ┌─────────▼──────────┐     ┌─────────▼──────────┐
         │  Static Assets     │     │   Application      │
         │  (Images, CSS, JS) │     │   Load Balancer    │
         └────────────────────┘     └─────────┬──────────┘
                                               │
                              ┌────────────────┼────────────────┐
                              │                                 │
                   ┌──────────▼─────────┐         ┌───────────▼──────────┐
                   │  Web Server 1      │         │  Web Server 2        │
                   │  ┌──────────────┐  │         │  ┌──────────────┐    │
                   │  │ Next.js App  │  │         │  │ Next.js App  │    │
                   │  │ (Port 3000)  │  │         │  │ (Port 3000)  │    │
                   │  └──────────────┘  │         │  └──────────────┘    │
                   │  ┌──────────────┐  │         │  ┌──────────────┐    │
                   │  │ Laravel API  │  │         │  │ Laravel API  │    │
                   │  │ (PHP-FPM)    │  │         │  │ (PHP-FPM)    │    │
                   │  └──────────────┘  │         │  └──────────────┘    │
                   │  ┌──────────────┐  │         │  ┌──────────────┐    │
                   │  │ Admin Panel  │  │         │  │ Admin Panel  │    │
                   │  │   (Blade)    │  │         │  │   (Blade)    │    │
                   │  └──────────────┘  │         │  └──────────────┘    │
                   └──────────┬─────────┘         └───────────┬──────────┘
                              │                               │
                              └───────────┬───────────────────┘
                                          │
                          ┌───────────────┼───────────────┐
                          │               │               │
                ┌─────────▼──────┐ ┌─────▼────────┐ ┌───▼──────────┐
                │  MySQL Master  │ │    Redis     │ │Queue Workers │
                │  (Write/Read)  │ │   Cluster    │ │ (Laravel)    │
                └────────┬───────┘ └──────────────┘ └──────────────┘
                         │
                ┌────────┴────────┐
                │                 │
        ┌───────▼──────┐  ┌──────▼───────┐
        │ MySQL Slave1 │  │ MySQL Slave2 │
        │   (Read)     │  │   (Read)     │
        └──────────────┘  └──────────────┘


┌────────────────────────────────────────────────────────────────┐
│                      BACKUP & MONITORING                        │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐           │
│  │   Backup    │  │   Sentry    │  │  New Relic  │           │
│  │   Server    │  │   Error     │  │     APM     │           │
│  │   (S3)      │  │  Tracking   │  │             │           │
│  └─────────────┘  └─────────────┘  └─────────────┘           │
│                                                                 │
└────────────────────────────────────────────────────────────────┘
```

## 5. CI/CD Pipeline

```
┌─────────────────────────────────────────────────────────────────┐
│                        GitHub Repository                         │
│                                                                  │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐               │
│  │  Backend   │  │  Frontend  │  │   Admin    │               │
│  │  (Laravel) │  │  (Next.js) │  │   (Blade)  │               │
│  └─────┬──────┘  └─────┬──────┘  └─────┬──────┘               │
└────────┼───────────────┼───────────────┼────────────────────────┘
         │               │               │
         │  Push to main branch          │
         └───────────────┴───────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                    GitHub Actions Workflow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Step 1: Code Quality                                           │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ • Run ESLint (Frontend)                                   │  │
│  │ • Run PHP CS Fixer (Backend)                             │  │
│  │ • Run Prettier                                            │  │
│  └──────────────────────────────────────────────────────────┘  │
│                         ▼                                        │
│  Step 2: Testing                                                │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Backend:                                                  │  │
│  │ • PHPUnit tests                                           │  │
│  │ • Feature tests                                           │  │
│  │ • API tests                                               │  │
│  │                                                            │  │
│  │ Frontend:                                                 │  │
│  │ • Jest unit tests                                         │  │
│  │ • React Testing Library                                   │  │
│  │ • Playwright E2E tests                                    │  │
│  └──────────────────────────────────────────────────────────┘  │
│                         ▼                                        │
│  Step 3: Build                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ • Build Docker images                                     │  │
│  │ • Optimize frontend bundle                                │  │
│  │ • Run composer install --optimize-autoloader              │  │
│  │ • Cache dependencies                                       │  │
│  └──────────────────────────────────────────────────────────┘  │
│                         ▼                                        │
│  Step 4: Deploy to Staging                                      │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ • Push images to registry                                 │  │
│  │ • Deploy to staging server                                │  │
│  │ • Run database migrations                                 │  │
│  │ • Run smoke tests                                         │  │
│  └──────────────────────────────────────────────────────────┘  │
│                         ▼                                        │
│  Step 5: Manual Approval (for production)                       │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ • Review staging environment                              │  │
│  │ • QA testing                                              │  │
│  │ • Approve deployment                                       │  │
│  └──────────────────────────────────────────────────────────┘  │
│                         ▼                                        │
│  Step 6: Deploy to Production                                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ • Blue-Green deployment                                   │  │
│  │ • Run database migrations                                 │  │
│  │ • Health checks                                           │  │
│  │ • Switch traffic to new version                           │  │
│  │ • Monitor errors                                          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                         ▼                                        │
│  Step 7: Post-Deployment                                        │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ • Clear caches                                            │  │
│  │ • Notify team (Slack)                                     │  │
│  │ • Create release tag                                       │  │
│  │ • Update documentation                                     │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

**Tài liệu này cung cấp cái nhìn tổng quan về kiến trúc hệ thống.**  
**Để biết thêm chi tiết về từng thành phần, vui lòng tham khảo các tài liệu khác.**

**Version:** 1.0  
**Last Updated:** 2025-10-27
