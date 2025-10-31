## Chat Real-time - Get New Messages (Guest Polling)

Purpose: Poll for new messages in a guest session after a given last message ID.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA

---

## 1) Endpoint
- **Method**: GET
- **Base URL**: https://api.[domain].com
- **Path**: /api/v1/chat/guest/{sessionId}/messages
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

#### Query Params
| Name | Type | Required | Default | Example | Description |
|------|------|----------|---------|---------|-------------|
| last_message_id | int | No | 0 | 120 | Return messages with id > this value |

---

## 2) Response

#### 200 OK
Returns array of `ChatMessageResource`.

Example:
```json
{
  "success": true,
  "message": "Messages retrieved",
  "data": [ /* ChatMessageResource[] */ ],
  "meta": null,
  "trace_id": "uuid",
  "timestamp": "2025-10-31T12:34:56Z"
}
```

Common errors: 404 NOT_FOUND | 429 RATE_LIMIT_EXCEEDED | 500 INTERNAL_ERROR

---

## 3) Flow Logic
- Find session by `session_key` → service fetches messages with id > `last_message_id` → return resources.

---

## 4) Database Impact
- Tables: `chat_messages` SELECT by session and id range.

---

## 5) Integrations
- Internal: `ChatRealTimeServiceInterface`

---

## 6) Security
- Public; do not leak unrelated session data.

---

## 7) Observability
- trace_id; polling metrics, throughput.

---

## 8) Performance
- Throttle 60/min to limit polling rate.

---

## 9) Edge Cases
- last_message_id missing → default 0.

---

## 10) Testing
- With and without last_message_id; nonexistent session.

---

## 11) Versioning
- Path versioned.

---

## 12) Changelog
- [2025-10-31] Initial spec – author


