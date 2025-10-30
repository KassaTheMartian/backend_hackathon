## API Name
Review: Approve Review (Admin, POST /api/v1/reviews/{id}/approve)

Purpose: Admin approves a pending user review, making it public/active. Used for moderation workflow.

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
- **Path**: /api/v1/reviews/{id}/approve
- **Auth**: Required (Bearer token / Sanctum, role: admin)
- **Rate limiting**: 60 req/minute

#### Headers
| Name          | Required | Example | Description       |
|---------------|----------|---------|-------------------|
| Authorization | Yes      | Bearer <token> | Admin authentication |
| Content-Type  | No       | application/json | Request format |

#### Path Params
| Name | Type | Required | Example | Description   |
|------|------|----------|---------|---------------|
| id   | int  | Yes      | 876     | Review ID     |

#### Request Body Schema
N/A
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
  "data": { "id": 876, "status": "approved", ... }
}
```
#### Common Error Codes
| HTTP | Internal code    | When it happens      | Frontend handling |
|------|------------------|----------------------|-------------------|
| 401  | UNAUTHORIZED     | Not logged in        | Prompt admin login|
| 403  | FORBIDDEN        | Not admin/authz      | Show error msg    |
| 404  | NOT_FOUND        | No such review       | Show error        |
| 500  | INTERNAL_ERROR   | Server error         | Retry/support     |
---
## 3) Flow Logic
- Authz: verify admin
- Find review, error if not found
- Approve review, change status to 'approved' (public)
- Return updated review

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[Admin POST /reviews/{id}/approve] --> B[Check admin auth]
    B -- No --> Z[401/403 Error]
    B -- Yes --> C[Find review by ID]
    C -- Not found --> Y[404 Error]
    C -- Found --> D[Change status to approved]
    D --> E[Return 200]
```
---
## 4) Database Impact
- Table: reviews (UPDATE, status)
---
## 5) Integrations & External Effects
- Approved review may trigger notifications
---
## 6) Security
- Admin only
---
## 7) Observability (Logging/Monitoring)
- Log moderation/approvals
---
## 8) Performance & Scalability
- Fast update
---
## 9) Edge Cases & Business Rules
- Can only approve if status is pending
---
## 10) Testing
- Non-admin, already approved, invalid id
- Example:
```bash
curl -X POST -H "Authorization: Bearer <token>" "https://api.example.com/api/v1/reviews/876/approve"
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
- [x] Schema/errors
- [x] Mermaid chart
- [x] DB impact
- [x] Security
- [x] Logging/metrics
- [x] Test/FE example
- [x] OpenAPI mapping
