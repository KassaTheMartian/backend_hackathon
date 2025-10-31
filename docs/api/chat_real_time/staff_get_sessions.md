## Chat Real-time - Staff Get Assigned Sessions

Purpose: Retrieve sessions assigned to the authenticated staff user.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA

---

## 1) Endpoint
- **Method**: GET
- **Base URL**: https://api.[domain].com
- **Path**: /api/v1/chat/staff/sessions
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

---

## 2) Response

#### 200 OK
Returns array of `ChatSessionResource` assigned to staff.

Common errors: 401 UNAUTHORIZED | 429 RATE_LIMIT_EXCEEDED | 500 INTERNAL_ERROR

---

## 3) Flow Logic
- Service fetches sessions by staff user id → return resources.

---

## 4) Database Impact
- `chat_sessions` SELECT filtered by `assigned_to`.

---

## 5) Integrations
- Internal: `ChatRealTimeServiceInterface`

---

## 6) Security
- Requires Sanctum auth; ensure user is staff.

---

## 7) Observability
- trace_id; throughput.

---

## 8) Performance
- Throttle 60/min.

---

## 9) Edge Cases
- No sessions assigned → empty list.

---

## 10) Testing
- Staff with sessions vs none.

---

## 11) Versioning
- Path versioned.

---

## 12) Changelog
- [2025-10-31] Initial spec – author


