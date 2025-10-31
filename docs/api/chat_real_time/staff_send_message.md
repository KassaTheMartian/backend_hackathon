## Chat Real-time - Staff Send Message

Purpose: Staff sends a message in a session they are assigned to.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA

---

## 1) Endpoint
- **Method**: POST
- **Base URL**: https://api.[domain].com
- **Path**: /api/v1/chat/sessions/{id}/staff-message
- **Environment**: dev | staging | prod
- **Auth**: Bearer JWT (Sanctum)
- **Required Scope/Role**: staff
- **Idempotency**: Not required
- **Rate limiting**: 60 req/minute
- **Caching**: None

#### Headers
| Name | Required | Example | Description |
|------|----------|---------|-------------|
| Authorization | Yes | Bearer <token> | Staff authentication |
| Content-Type | Yes | application/json | Payload |

#### Path Params
| Name | Type | Required | Example | Description |
|------|------|----------|---------|-------------|
| id | int | Yes | 55 | Session ID |

#### Request Body Schema
```json
{
  "message": "string (required)"
}
```

---

## 2) Response

#### 200 OK
Returns created message resource or 403 if staff not assigned.

Common errors: 401 UNAUTHORIZED | 403 FORBIDDEN | 404 NOT_FOUND | 400 VALIDATION_FAILED | 429 RATE_LIMIT_EXCEEDED | 500 INTERNAL_ERROR

---

## 3) Flow Logic
- Check session exists → verify staff assigned (`assigned_to`) → create message via service → return resource.

---

## 4) Database Impact
- `chat_messages` INSERT; `chat_sessions` SELECT

---

## 5) Integrations
- Internal: `ChatRealTimeServiceInterface`

---

## 6) Security
- Requires Sanctum auth and assignment validation.

---

## 7) Observability
- trace_id; error types.

---

## 8) Performance
- Throttle 60/min.

---

## 9) Edge Cases
- Staff not assigned → 403.

---

## 10) Testing
- Assigned vs not assigned; invalid token.

---

## 11) Versioning
- Path versioned.

---

## 12) Changelog
- [2025-10-31] Initial spec – author


