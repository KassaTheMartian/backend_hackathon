## Chat Real-time - Transfer To Human

Purpose: Transfer a guest chat session to a human staff member.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA

---

## 1) Endpoint
- **Method**: POST
- **Base URL**: https://api.[domain].com
- **Path**: /api/v1/chat/guest/{sessionId}/transfer-human
- **Environment**: dev | staging | prod
- **Auth**: None
- **Required Scope/Role**: -
- **Idempotency**: Not required
- **Rate limiting**: 60 req/minute
- **Caching**: None

#### Path Params
| Name | Type | Required | Example | Description |
|------|------|----------|---------|-------------|
| sessionId | string | Yes | 3f8a1b3e-... | Guest session key |

---

## 2) Response

#### 200 OK
On success returns assigned staff and message.

Example:
```json
{
  "success": true,
  "message": "Transfer successful",
  "data": {
    "staff": { /* staff summary */ },
    "message": "You are now connected to a human agent"
  },
  "meta": null,
  "trace_id": "uuid",
  "timestamp": "2025-10-31T12:34:56Z"
}
```

Common errors: 404 NOT_FOUND | 429 RATE_LIMIT_EXCEEDED | 500 INTERNAL_ERROR

---

## 3) Flow Logic
- Find session by `session_key` → service tries assignment → return status and staff info.

---

## 4) Database Impact
- Tables: `chat_sessions` UPDATE (assignment) possible; reads messages may occur.

---

## 5) Integrations
- Internal: `ChatRealTimeServiceInterface`

---

## 6) Security
- Public trigger; server-side checks for availability and assignment.

---

## 7) Observability
- trace_id; assignment metrics.

---

## 8) Performance
- Throttle 60/min.

---

## 9) Edge Cases
- No staff available → returns ok with message (per controller).

---

## 10) Testing
- Successful transfer; no staff available; invalid session.

---

## 11) Versioning
- Path versioned.

---

## 12) Changelog
- [2025-10-31] Initial spec – author


