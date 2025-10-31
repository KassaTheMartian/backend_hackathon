## Chat Real-time - Guest Send Message

Purpose: Send a message as a guest in an existing session.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA

---

## 1) Endpoint
- **Method**: POST
- **Base URL**: https://api.[domain].com
- **Path**: /api/v1/chat/guest/{sessionId}/message
- **Environment**: dev | staging | prod
- **Auth**: None
- **Required Scope/Role**: -
- **Idempotency**: Not required
- **Rate limiting**: 60 req/minute
- **Caching**: None

#### Headers
| Name | Required | Example | Description |
|------|----------|---------|-------------|
| Content-Type | Yes | application/json | Payload |

#### Path Params
| Name | Type | Required | Example | Description |
|------|------|----------|---------|-------------|
| sessionId | string | Yes | 3f8a1b3e-... | Guest session key |

#### Request Body Schema
```json
{
  "message": "string (required)"
}
```

Sample request:
```bash
curl -X POST "https://api.example.com/api/v1/chat/guest/3f8a1b3e-.../message" \
  -H "Content-Type: application/json" \
  -d '{"message": "Xin chào"}'
```

---

## 2) Response

#### 200 OK
Returns the created message resource.

Example:
```json
{
  "success": true,
  "message": "Message sent",
  "data": { /* ChatMessageResource */ },
  "meta": null,
  "trace_id": "uuid",
  "timestamp": "2025-10-31T12:34:56Z"
}
```

Common errors: 404 NOT_FOUND (session) | 400 VALIDATION_FAILED | 429 RATE_LIMIT_EXCEEDED | 500 INTERNAL_ERROR

---

## 3) Flow Logic
- Find session by `session_key` → build `ChatMessageData` with sender_type `user` → service persists → return resource.

---

## 4) Database Impact
- Tables: `chat_messages` INSERT; `chat_sessions` SELECT

---

## 5) Integrations
- Internal: `ChatRealTimeServiceInterface`

---

## 6) Security
- Public; sanitize input; avoid logging content.

---

## 7) Observability
- trace_id, latency.

---

## 8) Performance
- Throttle 60/min.

---

## 9) Edge Cases
- Invalid/non-existent session → 404.

---

## 10) Testing
- Valid send; empty message; missing session.

---

## 11) Versioning
- Path versioned.

---

## 12) Changelog
- [2025-10-31] Initial spec – author


