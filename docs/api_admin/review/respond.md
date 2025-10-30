## API Name
Review: Admin Respond to Review (Admin, POST /api/v1/reviews/{id}/respond)

Purpose: Allows an admin to respond to a user's review — e.g. sending thanks, addressing complaints. Admin comment gets associated to the review.

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
- **Path**: /api/v1/reviews/{id}/respond
- **Auth**: Required (Bearer token, admin)
- **Rate limiting**: 60 req/minute

#### Headers
| Name          | Required | Example         | Description         |
|---------------|----------|-----------------|---------------------|
| Authorization | Yes      | Bearer <token>  | Admin authentication|
| Content-Type  | Yes      | application/json| JSON encoded        |

#### Path Params
| Name | Type | Required | Example | Description   |
|------|------|----------|---------|---------------|
| id   | int  | Yes      | 512     | Review ID     |

#### Request Body Schema
```json
{
  "admin_response": "string, required, max 1000"
}
```
---
#### Query Params
N/A
---
## 2) Response
#### Standard error envelope
```json
{
  "success": false,
  "message": "Forbidden/not found/validation error",
  "code": "ERROR_CODE",
  "errors": {},
  "trace_id": "uuid"
}
```
#### 200 Success Example
```json
{
  "success": true,
  "data": {"id": 512, "admin_response": "Thank you for your feedback"}
}
```
#### Common Error Codes
| HTTP | Internal code    | When it happens              | Frontend handling |
|------|------------------|------------------------------|-------------------|
| 401  | UNAUTHORIZED     | Not logged in/admin          | Prompt login      |
| 403  | FORBIDDEN        | Not admin                    | Show error        |
| 404  | NOT_FOUND        | No review                    | Show error msg    |
| 422  | VALIDATION_ERROR | Empty/missing response       | Show field error  |
| 500  | INTERNAL_ERROR   | Server error                 | Retry             |
---
## 3) Flow Logic
- Check authentication/role
- Find review by id, error if not found
- Validate admin_response (required, max 1000)
- Save response to review
- Return updated review

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[Admin POST /reviews/{id}/respond] --> B[Authz: Admin?]
    B -- No --> Z[401/403]
    B -- Yes --> C[Find review by id]
    C -- Not found --> Y[404]
    C -- Found --> D[Validate admin_response]
    D -- Invalid --> X[422]
    D -- Valid --> E[Save response to review]
    E --> F[Return 200]
```
---
## 4) Database Impact
- Table: reviews (UPDATE admin_response)
---
## 5) Integrations & External Effects
- Possible notification to user
---
## 6) Security
- Admin only
---
## 7) Observability (Logging/Monitoring)
- Log admin comment activity
---
## 8) Performance & Scalability
- Fast write
---
## 9) Edge Cases & Business Rules
- Must not be empty/overlong
---
## 10) Testing
- Blank, overlong, not admin, not found
- Example:
```bash
curl -X POST -H "Authorization: Bearer <token>" "https://api.example.com/api/v1/reviews/512/respond" -d '{"admin_response":"Thank you for your feedback"}'
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
