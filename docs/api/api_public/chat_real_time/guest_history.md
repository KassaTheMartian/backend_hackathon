## Chat Real-time - Get Guest History

Purpose: Retrieve chat history for a guest by session ID.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA

---

## 1) Endpoint
- **Method**: GET
- **Base URL**: https://api.[domain].com
- **Path**: /api/v1/chat/guest/{sessionId}/history
- **Environment**: dev | staging | prod
- **Auth**: None
- **Required Scope/Role**: -
- **Idempotency**: N/A
- **Rate limiting**: 60 req/minute
- **Caching**: None

#### Path Params
| Name | Type | Required | Example | Description |
|------|------|----------|---------|-------------|
| sessionId | string | Yes | 3f8a1b3e-... | Guest session key |

---

## 2) Response

#### 200 OK
Returns session info and messages array.

Example:
```json
{
  "success": true,
  "message": "History retrieved",
  "data": {
    "session": { /* ChatSessionResource */ },
    "messages": [ /* ChatMessageResource[] */ ]
  },
  "meta": null,
  "trace_id": "uuid",
  "timestamp": "2025-10-31T12:34:56Z"
}
```

Common errors: 404 NOT_FOUND | 429 RATE_LIMIT_EXCEEDED | 500 INTERNAL_ERROR

---

## 3) Flow Logic
- Load session by `session_key` → if not found return empty result → load messages ordered by id → transform resources.

---

## 4) Database Impact
- Tables: `chat_sessions` SELECT, `chat_messages` SELECT

---

## 5) Integrations
- Internal: `ChatRealTimeServiceInterface`

---

## 6) Security
- Public read; no PII in logs.

---

## 7) Observability
- trace_id; latency.

---

## 8) Performance
- Throttle 60/min; no caching.

---

## 9) Edge Cases
- Nonexistent session → empty messages.

---

## 10) Testing
- Valid session; missing session; large history.

---

## 11) Versioning
- Path versioned.

---

## 12) Changelog
- [2025-10-31] Initial spec – author


