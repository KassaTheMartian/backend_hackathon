## Chatbot - Get Conversation History

Purpose: Retrieve chatbot conversation history for the current authenticated user or a guest identified by `session_key`.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA
- **Related epic/ticket**: -

---

## 1) Endpoint
- **Method**: GET
- **Base URL**: https://api.[domain].com
- **Path**: /api/v1/chatbot/history
- **Environment**: dev | staging | prod
- **Auth**: None | Bearer JWT
- **Required Scope/Role**: -
- **Idempotency**: Not applicable
- **Rate limiting**: 60 req/minute
- **Caching**: None

#### Headers
| Name | Required | Example | Description |
|------|----------|---------|-------------|
| Authorization | No | Bearer <token> | Optional; associates lookup to authenticated user |
| X-Chat-Session | No | 3f8a1b3e-... | Optional guest session key (alternative to query) |

#### Path Params
| Name | Type | Required | Example | Description |
|------|------|----------|---------|-------------|

#### Query Params
| Name | Type | Required | Default | Example | Description |
|------|------|----------|---------|---------|-------------|
| session_key | string | No | - | 3f8a1b3e-... | Guest session key (if not authenticated) |

#### Request Body Schema
N/A

---

## 2) Response

#### Error envelope (common)
```json
{
  "success": false,
  "message": "Short error description",
  "error": { "type": "ERROR_TYPE", "code": "ERROR_CODE" },
  "trace_id": "uuid",
  "timestamp": "2025-10-31T12:34:56Z"
}
```

#### 200 Success
Schema:
- success: boolean
- message: string
- data: object
  - session_key: string
  - messages: array of ChatMessage
- ChatMessage fields (per resource):
  - id: int
  - session_id: int
  - sender_id: int|null
  - sender_type: string|null
  - message: string
  - message_type: string|null
  - is_bot: boolean|null
  - bot_confidence: number|null
  - metadata: object|null
  - read_at: datetime|null
  - created_at: datetime
  - updated_at: datetime
  - sender: object|null (present when relationship loaded)

Example (no session):
```json
{
  "success": true,
  "message": "Response generated successfully",
  "data": {
    "messages": []
  },
  "meta": null,
  "trace_id": "b2f3e7a1-6a40-4b78-9e10-9b2f5a4d9c11",
  "timestamp": "2025-10-31T12:34:56Z"
}
```

Example (with session):
```json
{
  "success": true,
  "message": "Response generated successfully",
  "data": {
    "session_key": "3f8a1b3e-0c6f-4b1a-9f3a-8b1e2d7f9c21",
    "messages": [
      {
        "id": 101,
        "session_id": 55,
        "sender_id": 1,
        "sender_type": "user",
        "message": "Xin chào",
        "message_type": "text",
        "is_bot": false,
        "bot_confidence": null,
        "metadata": null,
        "read_at": null,
        "created_at": "2025-10-31T12:30:00Z",
        "updated_at": "2025-10-31T12:30:00Z",
        "sender": null
      }
    ]
  },
  "meta": null,
  "trace_id": "b2f3e7a1-6a40-4b78-9e10-9b2f5a4d9c11",
  "timestamp": "2025-10-31T12:34:56Z"
}
```

#### Common error codes
| HTTP | Internal code | When it happens | Frontend handling |
|------|---------------|-----------------|-------------------|
| 401 | UNAUTHORIZED | Invalid token (if provided) | Prompt login |
| 429 | RATE_LIMIT_EXCEEDED | Rate limit exceeded | Show retry-after |
| 500 | INTERNAL_ERROR | Server error | Retry / contact support |

I18n/Localization: Localized messages via `SetLocale` middleware and translation keys.

---

## 3) Flow Logic
- Step 1: If authenticated, load session by `user_id`
- Step 2: Else, get `session_key` from query or `X-Chat-Session` and load by `session_key`
- Step 3: If no session → return empty messages
- Step 4: Load messages ordered by id and transform via `ChatMessageResource`
- Step 5: Return standardized envelope

Mermaid Flow:
```mermaid
flowchart TD
  A[Client Request] --> B{Authenticated?}
  B -- Yes --> C[Find session by user_id]
  B -- No --> D[Read session_key from query/header]
  D --> E{Have session_key?}
  E -- No --> F[Return messages: []]
  E -- Yes --> G[Find session by session_key]
  C --> H[Load messages order by id]
  G --> H
  H --> I[Resource transform]
  I --> J[Return 200]
```

---

## 4) Database Impact
- Primary tables: `chat_sessions` (SELECT), `chat_messages` (SELECT)
- Related tables: users (`user_id`)
- History/tracking: chronological message history
- Indexes: session_key, foreign keys to user/session
- Transactions: no
- Constraints: session_key length, referential integrity

Related migration(s): refer to `database/migrations/*chat*`

---

## 5) Integrations & External Effects
- Internal services: Eloquent models + Resources
- External services: -
- Emitted events: -
- Webhook: -

---

## 6) Security
- Auth/Z: Optional Bearer JWT
- Sensitive data: mask PII in logs
- Anti-replay/CSRF: N/A
- Input hardening: session_key length validation client-side
- Rate limit/quota: 60 req/min

---

## 7) Observability (Logging/Monitoring)
- Logs: trace_id, request context
- Metrics: latency, error rate, throughput
- Alerts: elevated error rate/latency

---

## 8) Performance & Scalability
- Expected QPS: standard public read endpoint
- Timeout: default
- Batch/bulk: N/A
- Caching strategy: none (history is user/session-specific)

---

## 9) Edge Cases & Business Rules
- No session for guest → empty messages
- Multiple sessions per user not handled here (first session returned)
- Messages ordered by id ascending in response

---

## 10) Testing
- Main cases: with auth, with guest session_key, missing session_key for guest, no session
- Postman/cURL snippets: see below

Quick cURL examples:
```bash
curl "https://api.example.com/api/v1/chatbot/history"

curl -H "X-Chat-Session: 3f8a1b3e-..." \
  "https://api.example.com/api/v1/chatbot/history?session_key=3f8a1b3e-..."
```

Frontend example (fetch):
```javascript
const url = new URL('/api/v1/chatbot/history', location.origin);
if (sessionKey) url.searchParams.set('session_key', sessionKey);
const res = await fetch(url.toString(), { headers: { 'X-Chat-Session': sessionKey || '' } });
const json = await res.json();
```

---

## 11) Versioning & Deprecation
- Versioning strategy: path-based (`/api/v1/...`)
- Deprecation plan: none

---

## 12) Changelog
- [2025-10-31] Initial spec – author

---

## 13) OpenAPI/Swagger Mapping
- Reference: included via `@OA\Get(path="/api/v1/chatbot/history")` in `App\Http\Controllers\Api\V1\ChatbotController`
- Component schema: response envelope `ApiEnvelope`

---

## 14) Completion Checklist
- [x] Endpoint, method, auth, headers clear
- [x] Request schema + validation complete
- [x] Response schema + standard error codes
- [x] Flow logic + business rules documented
- [x] Database impact (tables, indexes, transactions)
- [x] Security, rate limit
- [x] Logging/metrics/alerts
- [x] Performance, caching
- [x] Test cases and FE integration examples
- [x] OpenAPI mapping updated


