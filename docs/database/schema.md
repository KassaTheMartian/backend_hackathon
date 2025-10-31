## Database Schema and Relationships

This document outlines the main tables, columns, indexes, and relationships, based on current models and migrations. Use it as a quick reference alongside the ERD.

### users
- id (PK, bigint)
- name (string)
- email (string, unique)
- phone (string, nullable)
- is_admin (bool, default false)
- password (string)
- remember_token (string, nullable)
- meta (json, nullable)
- timestamps

Indexes: email unique
Relations: hasMany bookings, reviews, payments, chat_sessions, promotion_usages

### branches
- id (PK)
- name (json)
- slug (string, unique?)
- address (json)
- phone (string)
- email (string)
- opening_hours (json, nullable)
- latitude (decimal), longitude (decimal)
- is_active (bool)
- display_order (int)
- timestamps

Relations: hasMany bookings, staff, contact_submissions; belongsToMany services

### service_categories
- id (PK)
- name (json)
- slug (string, unique)
- is_active (bool)
- timestamps

Relations: hasMany services

### services
- id (PK)
- service_category_id (FK → service_categories.id)
- name (json)
- description (json, nullable)
- price (int)
- duration (int, minutes)
- is_featured (bool)
- is_active (bool)
- timestamps

Indexes: service_category_id
Relations: belongsTo service_categories; hasMany bookings; belongsToMany branches; belongsToMany staff

### staff
- id (PK)
- user_id (FK → users.id)
- branch_id (FK → branches.id)
- name (string)
- role (string)
- meta (json, nullable)
- timestamps

Indexes: user_id, branch_id
Relations: belongsTo user, branch; belongsToMany services; hasMany bookings

### bookings
- id (PK)
- user_id (FK → users.id)
- branch_id (FK → branches.id)
- staff_id (FK → staff.id, nullable)
- service_id (FK → services.id)
- start_time (datetime)
- duration (int)
- status (string)
- code (string, unique?)
- meta (json, nullable)
- timestamps

Indexes: user_id, branch_id, staff_id, service_id, start_time, status
Relations: belongsTo user, branch, staff, service; hasMany booking_status_histories; hasMany payments

### booking_status_histories
- id (PK)
- booking_id (FK → bookings.id)
- status (string)
- note (text, nullable)
- created_at, updated_at

Indexes: booking_id
Relations: belongsTo booking

### payments
- id (PK)
- user_id (FK → users.id)
- booking_id (FK → bookings.id, nullable)
- method (enum: cash, vnpay)
- status (string)
- amount (int)
- transaction_id (string)
- metadata (json)
- timestamps

Indexes: user_id, booking_id, transaction_id
Relations: belongsTo user, booking

### promotions
- id (PK)
- code (string, unique)
- discount_amount (int)
- starts_at (datetime)
- ends_at (datetime)
- is_active (bool)
- timestamps

### promotion_usages
- id (PK)
- promotion_id (FK → promotions.id)
- user_id (FK → users.id)
- booking_id (FK → bookings.id, nullable)
- timestamps

Indexes: promotion_id, user_id, booking_id
Relations: belongsTo promotion, user, booking

### posts
- id (PK)
- post_category_id (FK → post_categories.id)
- title (string)
- slug (string, unique)
- excerpt (text, nullable)
- content (longtext)
- is_featured (bool)
- views_count (int)
- timestamps

Indexes: post_category_id, slug
Relations: belongsTo post_category; belongsToMany post_tags

### post_categories
- id (PK)
- name (string)
- slug (string, unique)
- is_active (bool)
- timestamps

### post_tags
- id (PK)
- name (string)
- slug (string, unique)
- timestamps

### post_tag_post (pivot)
- post_id (FK → posts.id)
- post_tag_id (FK → post_tags.id)
Indexes: composite (post_id, post_tag_id)

### chat_sessions
- id (PK)
- user_id (FK → users.id, nullable)
- session_key (string, unique)
- meta (json, nullable)
- last_activity (datetime, nullable)
- is_active (bool)
- timestamps

Relations: hasMany chat_messages; belongsTo user

### chat_messages
- id (PK)
- chat_session_id (FK → chat_sessions.id)
- user_id (FK → users.id, nullable)
- role (string: user|assistant|staff)
- message (text)
- meta (json, nullable)
- timestamps

Indexes: chat_session_id, user_id
Relations: belongsTo chat_session; optional belongsTo user

### contact_submissions
- id (PK)
- name (string)
- email (string)
- phone (string)
- message (text)
- branch_id (FK → branches.id, nullable)
- timestamps

### otp_verifications
- id (PK)
- channel (string: email|phone)
- recipient (string)
- otp (string)
- expires_at (datetime)
- created_at, updated_at

### settings
- id (PK)
- key (string, unique)
- value (json)
- timestamps

### activity_logs
- id (PK)
- user_id (FK → users.id, nullable)
- action (string)
- context (json)
- timestamps

---

## Relationship Summary

- users 1—* bookings, reviews, payments, chat_sessions, promotion_usages
- branches 1—* bookings, staff, contact_submissions; branches *—* services
- services 1—* bookings; services *—* branches; services *—* staff; services *—1 service_categories
- staff 1—* bookings; staff *—* services
- bookings 1—* booking_status_histories; 1—* payments; *—1 users/branches/services/(staff?)
- promotions 1—* promotion_usages
- posts *—1 post_categories; posts *—* post_tags
- chat_sessions 1—* chat_messages; *—1 users (optional)

Notes
- Exact column types and constraints can be confirmed in `database/migrations/*`.
- Seeders/factories establish realistic demo data and index usage patterns.


