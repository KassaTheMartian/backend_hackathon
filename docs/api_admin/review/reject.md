## API Name
Review: Reject Review (Admin, POST /api/v1/reviews/{id}/reject)

Purpose: Lets an admin reject a user review for policy, inappropriateness, or spam. Requires reason explanation.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: admin | backend dev | QA
- **Related epic/ticket**: [TBD]
---
## 1) Endpoint
- **Method**: POST
- **Base URL**: https://api.example.com
- **Path**: /api/v1/reviews/{id}/reject
- **Auth**: Required (Bearer token / Sanctum, admin)
- **Rate limiting**: 60 req/minute

#### Headers
| Name          | Required | Example         | Description         |
|---------------|----------|-----------------|---------------------|
| Authorization | Yes      | Bearer <token>  | Admin authentication|
| Content-Type  | Yes      | application/json| JSON encoded        |

#### Path Params
| Name | Type | Required | Example | Description   |
|------|------|----------|---------|---------------|
| id   | int  | Yes      | 118     | Review ID     |

#### Request Body Schema
```json
{
  "reason": "string, required"
}
```
- reason: required, string (reason for rejection)

---
#### Query Params
N/A
---
## 2) Response
#### Standard error envelope
```json
{
  "success": false,
  "message": "Forbidden/not found/error",
  "code": "ERROR_CODE",
  "errors": {},
  "trace_id": "uuid"
}
```
#### 200 Success Example
```json
{
  "success": true,
  "data": {"id": 118, "status": "rejected", "reason": "Spam detected"}
}
```
#### Common Error Codes
| HTTP | Internal code    | When it happens              | Frontend handling |
|------|------------------|------------------------------|-------------------|
| 401  | UNAUTHORIZED     | Not logged in/admin          | Prompt login      |
| 403  | FORBIDDEN        | Not admin                    | Show error        |
| 404  | NOT_FOUND        | No such review               | Show error        |
| 422  | VALIDATION_ERROR | Missing/invalid reason       | Show error        |
| 500  | INTERNAL_ERROR   | Server error                 | Retry             |
---
## 3) Flow Logic
- Authz: verify admin
- Find review, must exist
- Validate reason
- Set status = rejected, log reason
- Return updated review

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[Admin POST /reviews/{id}/reject] --> B[Check admin auth]
    B -- No --> Z[401/403]
    B -- Yes --> C[Find review]
    C -- Not found --> Y[404]
    C -- Found --> D[Validate reason]
    D -- Invalid --> X[422]
    D -- OK --> E[Change status to rejected]
    E --> F[Return 200]
```
---
## 4) Database Impact
- Table: reviews (UPDATE, rejected)
---
## 5) Integrations & External Effects
- Reason saved for audit/log
---
## 6) Security
- Admin, reason required
---
## 7) Observability (Logging/Monitoring)
- Log rejections, audit
---
## 8) Performance & Scalability
- Fast update
---
## 9) Edge Cases & Business Rules
- Only pending reviews can be rejected
---
## 10) Testing
- Missing/invalid reason, non-admin, not-found
- Example:
```bash
curl -X POST -H "Authorization: Bearer <token>" "https://api.example.com/api/v1/reviews/118/reject" -d '{"reason":"Spam detected"}'
```
---
## 11) Versioning & Deprecation
- v1
---
## 12) Changelog
- [2025-10-30] Initial admin version
---
## 13) OpenAPI/Swagger Mapping
- Component: ReviewResource, ApiEnvelope
---
## 14) Completion Checklist
- [x] Endpoint/admin only
- [x] Request schema/errors
- [x] Mermaid chart
- [x] DB impact
- [x] Security
- [x] Logging/metrics
- [x] Test/FE example
- [x] OpenAPI mapping
