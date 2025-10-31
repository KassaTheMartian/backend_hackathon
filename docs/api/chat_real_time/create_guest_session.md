## Chat Real-time - Create Guest Session

Purpose: Create a guest chat session to start a real-time conversation.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA

---

## 1) Endpoint
- **Method**: POST
- **Base URL**: https://api.[domain].com
- **Path**: /api/v1/chat/guest/session
- **Environment**: dev | staging | prod
- **Auth**: None
- **Required Scope/Role**: -
- **Idempotency**: Session creation should use a unique `session_id`
- **Rate limiting**: 60 req/minute
- **Caching**: None

#### Headers
| Name | Required | Example | Description |
|------|----------|---------|-------------|
| Content-Type | Yes | application/json | Payload format |

#### Request Body Schema
```json
{
  "session_id": "string (required)",
  "guest_name": "string (optional)",
  "guest_email": "string (optional)",
  "guest_phone": "string (optional)"
}
```

Sample request:
```bash
curl -X POST "https://api.example.com/api/v1/chat/guest/session" \
  -H "Content-Type: application/json" \
  -d '{
    "session_id": "3f8a1b3e-0c6f-4b1a-9f3a-8b1e2d7f9c21",
    "guest_name": "Guest"
  }'
```

---

## 2) Response

#### 201 Created
Returns the created session and initial messages (if any).

Example:
```json
{
  "success": true,
  "message": "Session created successfully",
  "data": {
    "session": { /* ChatSessionResource */ },
    "messages": []
  },
  "meta": null,
  "trace_id": "uuid",
  "timestamp": "2025-10-31T12:34:56Z"
}
```

Common error codes: 400 VALIDATION_FAILED | 429 RATE_LIMIT_EXCEEDED | 500 INTERNAL_ERROR

---

## 3) Flow Logic
- Validate request → build `ChatSessionData` → service creates session → return `ChatSessionResource` and messages.

---

## 4) Database Impact
- Tables: `chat_sessions` INSERT; `chat_messages` optional SELECT
- Indexes: `session_key` unique/indexed

---

## 5) Integrations & External Effects
- Internal: `ChatRealTimeServiceInterface`

---

## 6) Security
- Public endpoint; avoid logging PII.

---

## 7) Observability
- trace_id, latency, error rate.

---

## 8) Performance & Scalability
- Throttle 60/min; no caching.

---

## 9) Edge Cases & Business Rules
- Duplicate `session_id` should be handled gracefully.

---

## 10) Testing
- Create with minimal fields; duplicate `session_id`.

---

## 11) Versioning & Deprecation
- Path versioned.

---

## 12) Changelog
- [2025-10-31] Initial spec – author


