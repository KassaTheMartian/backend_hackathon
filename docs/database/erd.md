## Database ERD (Mermaid)

This diagram reflects the current domain entities and relationships inferred from models and migrations.

```mermaid
erDiagram
  USERS ||--o{ BOOKINGS : has
  USERS ||--o{ REVIEWS : writes
  USERS ||--o{ PAYMENTS : makes
  USERS ||--o{ CHAT_SESSIONS : owns
  USERS ||--o{ PROMOTION_USAGES : uses
  

  BRANCHES ||--o{ BOOKINGS : hosts
  BRANCHES ||--o{ STAFF : employs
  BRANCHES ||--o{ CONTACT_SUBMISSIONS : receives
  BRANCHES }o--o{ SERVICES : offers

  SERVICES ||--o{ BOOKINGS : includes
  SERVICE_CATEGORIES ||--o{ SERVICES : categorizes

  STAFF ||--o{ BOOKINGS : serves
  STAFF }o--o{ SERVICES : provides

  BOOKINGS ||--o{ BOOKING_STATUS_HISTORIES : has
  BOOKINGS ||--o{ PAYMENTS : relates

  PROMOTIONS ||--o{ PROMOTION_USAGES : applied

  
  POST_CATEGORIES ||--o{ POSTS : categorizes
  POSTS }o--o{ POST_TAGS : tagged

  CHAT_SESSIONS ||--o{ CHAT_MESSAGES : has

  SETTINGS ||--o{ ACTIVITY_LOGS : logs

  OTP_VERIFICATIONS ||--o{ USERS : for

  USERS {
    bigint id PK
    string name
    string email
    string phone
    boolean is_admin
    string meta
    datetime created_at
    datetime updated_at
  }

  BRANCHES {
    bigint id PK
    string name
    string address
    string phone
    string email
    string opening_hours
    float latitude
    float longitude
    boolean is_active
    int display_order
    datetime created_at
    datetime updated_at
  }

  SERVICES {
    bigint id PK
    bigint service_category_id
    string name
    string description
    int price
    int duration
    boolean is_featured
    boolean is_active
    datetime created_at
    datetime updated_at
  }

  SERVICE_CATEGORIES {
    bigint id PK
    string name
    string slug
    boolean is_active
    datetime created_at
    datetime updated_at
  }

  STAFF {
    bigint id PK
    bigint user_id
    bigint branch_id
    string name
    string role
    string meta
    datetime created_at
    datetime updated_at
  }

  BOOKINGS {
    bigint id PK
    bigint user_id
    bigint branch_id
    bigint staff_id
    bigint service_id
    datetime start_time
    int duration
    string status
    string meta
    datetime created_at
    datetime updated_at
  }

  BOOKING_STATUS_HISTORIES {
    bigint id PK
    bigint booking_id
    string status
    text note
    datetime created_at
    datetime updated_at
  }

  PAYMENTS {
    bigint id PK
    bigint user_id
    bigint booking_id
    string method
    string status
    string transaction_id
    string metadata
    datetime created_at
    datetime updated_at
  }

  PROMOTIONS {
    bigint id PK
    string code
    int discount_amount
    datetime starts_at
    datetime ends_at
    boolean is_active
    datetime created_at
    datetime updated_at
  }

  PROMOTION_USAGES {
    bigint id PK
    bigint promotion_id
    bigint user_id
    bigint booking_id
    datetime created_at
    datetime updated_at
  }

  POSTS {
    bigint id PK
    bigint post_category_id
    string title
    string slug
    text content
    boolean is_featured
    int views_count
    datetime created_at
    datetime updated_at
  }

  POST_CATEGORIES {
    bigint id PK
    string name
    string slug
    boolean is_active
    datetime created_at
    datetime updated_at
  }

  POST_TAGS {
    bigint id PK
    string name
    string slug
    datetime created_at
    datetime updated_at
  }

  CHAT_SESSIONS {
    bigint id PK
    bigint user_id
    string session_key
    string meta
    datetime last_activity
    boolean is_active
    datetime created_at
    datetime updated_at
  }

  CHAT_MESSAGES {
    bigint id PK
    bigint chat_session_id
    bigint user_id
    string role
    text message
    string meta
    datetime created_at
    datetime updated_at
  }

  CONTACT_SUBMISSIONS {
    bigint id PK
    string name
    string email
    string phone
    text message
    datetime created_at
    datetime updated_at
  }

  OTP_VERIFICATIONS {
    bigint id PK
    string email_or_phone
    string otp
    datetime expires_at
    datetime created_at
    datetime updated_at
  }

  SETTINGS {
    bigint id PK
    string key
    string value
    datetime created_at
    datetime updated_at
  }

  ACTIVITY_LOGS {
    bigint id PK
    bigint user_id
    string action
    string context
    datetime created_at
    datetime updated_at
  }
```

Notes
- Many-to-many: `branches`↔`services`, `staff`↔`services` realized via pivot tables (not expanded here).
- Some fields/types inferred from models and typical conventions.


