## Chat Real-time - Staff Get Session Messages

Purpose: Staff fetches new messages for a specific session they are assigned to.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA

---

## 1) Endpoint
- **Method**: GET
- **Base URL**: https://api.[domain].com
- **Path**: /api/v1/chat/sessions/{id}/messages
- **Environment**: dev | staging | prod
- **Auth**: Bearer JWT (Sanctum)
- **Required Scope/Role**: staff
- **Idempotency**: N/A
- **Rate limiting**: 60 req/minute
- **Caching**: None

#### Headers
| Name | Required | Example | Description |
|------|----------|---------|-------------|
| Authorization | Yes | Bearer <token> | Staff authentication |

#### Path Params
| Name | Type | Required | Example | Description |
|------|------|----------|---------|-------------|
| id | int | Yes | 55 | Session ID |

#### Query Params
| Name | Type | Required | Default | Example | Description |
|------|------|----------|---------|---------|-------------|
| last_message_id | int | No | 0 | 120 | Return messages with id > this value |

---

## 2) Response

#### 200 OK
Returns array of `ChatMessageResource`.

Common errors: 401 UNAUTHORIZED | 403 FORBIDDEN | 404 NOT_FOUND | 429 RATE_LIMIT_EXCEEDED | 500 INTERNAL_ERROR

---

## 3) Flow Logic
- Ensure session exists → check staff assignment (`assigned_to`) → fetch messages with id > `last_message_id` → return resources.

---

## 4) Database Impact
- `chat_messages` SELECT by session and id range.

---

## 5) Integrations
- Internal: `ChatRealTimeServiceInterface`

---

## 6) Security
- Requires Sanctum auth and assignment validation.

---

## 7) Observability
- trace_id; staff polling metrics.

---

## 8) Performance
- Throttle 60/min.

---

## 9) Edge Cases
- Not assigned → 403.

---

## 10) Testing
- Assigned vs not assigned; with and without last_message_id.

---

## 11) Versioning
- Path versioned.

---

## 12) Changelog
- [2025-10-31] Initial spec – author


