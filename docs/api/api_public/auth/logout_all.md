## API Name
Auth: Logout All Sessions (POST /api/v1/auth/logout-all)

Purpose: Revokes all authentication tokens for the current user. Useful for logging out from all devices or force log out when lost/stolen device.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA | customer
- **Related epic/ticket**: [TBD]
---
## 1) Endpoint
- **Method**: POST
- **Base URL**: https://api.example.com
- **Path**: /api/v1/auth/logout-all
- **Environment**: dev | staging | prod
- **Auth**: Bearer token / Sanctum required
- **Rate limiting**: 60 req/minute

#### Headers
| Name          | Required | Example               | Description             |
|---------------|----------|----------------------|-------------------------|
| Authorization | Yes      | Bearer <token>       | User authentication     |
| Content-Type  | Yes      | application/json     | Request body format     |

#### Path Params
N/A
#### Query Params
N/A
#### Request Body Schema
N/A
---
## 2) Response
#### 200 Success Example
```json
{
  "success": true,
  "data": { "message": "Logged out from all devices" }
}
```
#### Error Envelope
```json
{
  "success": false,
  "message": "Short error description",
  "code": "ERROR_CODE",
  "errors": {},
  "trace_id": "uuid"
}
```
#### Common Error Codes
| HTTP | Internal code       | When it happens                      | Frontend handling         |
|------|---------------------|--------------------------------------|---------------------------|
| 401  | UNAUTHORIZED        | No/invalid token                     | Redirect/login            |
| 500  | INTERNAL_ERROR      | Server error                         | Retry/support             |

---
## 3) Flow Logic
- Authenticate token
- Revoke all tokens for the user
- Respond 200 with confirmation message

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[Request with Auth Token] --> B{Token Valid?}
    B -- No --> Z[401 Unauthorized]
    B -- Yes --> C[Revoke All Tokens for User]
    C --> D[Return 200 Success]
```
---
## 4) Database Impact
- Table: tokens (delete/revoke all for user)
---
## 5) Integrations & External Effects
None
---
## 6) Security
- Requires valid auth token
---
## 7) Observability (Logging/Monitoring)
- Log user/id and time
---
## 8) Performance & Scalability
- One batch write operation
---
## 9) Edge Cases & Business Rules
- All tokens must be revoked even if session count > 1
---
## 10) Testing
- Normal logout-all, call with wrong/expired token, verify tokens revoked on all devices
- Example:
```bash
curl -X POST "https://api.example.com/api/v1/auth/logout-all" -H "Authorization: Bearer <token>"
```
---
## 11) Versioning & Deprecation
- v1
---
## 12) Changelog
- [2025-10-30] Initial version – AI generated, ENGLISH
---
## 13) OpenAPI/Swagger Mapping
- Component: ApiEnvelope
---
## 14) Completion Checklist
- [x] Endpoint clear
- [x] Request schema + validation
- [x] Response schema + error codes
- [x] Flow logic described
- [x] DB impact
- [x] Security docs
- [x] Logging/metrics
- [x] Performance notes
- [x] Test/FE example
- [x] OpenAPI mapping
