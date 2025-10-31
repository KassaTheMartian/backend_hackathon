## API Name
Review: Get Review by ID (GET /api/v1/reviews/{id})

Purpose: Fetches the detail (text, rating, user, creation date, service, staff, etc.) for a specific review.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: frontend dev | backend dev | customer | QA
- **Related epic/ticket**: [TBD]
---
## 1) Endpoint
- **Method**: GET
- **Base URL**: https://api.example.com
- **Path**: /api/v1/reviews/{id}
- **Auth**: None
- **Rate limiting**: 60 req/minute
- **Caching**: Optionally cache on FE

#### Headers
| Name         | Required | Example            | Description        |
|--------------|----------|--------------------|--------------------|
| Content-Type | No       | application/json   | Request format     |

#### Path Params
| Name | Type | Required | Example | Description     |
|------|------|----------|---------|-----------------|
| id   | int  | Yes      | 289     | Review ID       |

#### Request Body Schema
N/A
---
## 2) Response
#### Standard error envelope
```json
{
  "success": false,
  "message": "Review not found",
  "code": "NOT_FOUND",
  "errors": {},
  "trace_id": "uuid"
}
```
#### 200 Success Example
```json
{
  "success": true,
  "data": {
    "id": 289,
    "service_id": 25,
    "rating": 5,
    "comment": "Excellent experience!",
    "staff": {"id":9, "name":"Jane Staff"},
    "created_at": "2025-10-30T13:22:00Z",
    ...
  }
}
```
#### Common Error Codes
| HTTP | Internal code    | When it happens   | Frontend handling |
|------|------------------|-------------------|-------------------|
| 404  | NOT_FOUND        | No such review    | Show error        |
| 500  | INTERNAL_ERROR   | Server error      | Retry/support     |
---
## 3) Flow Logic
- Validate ID
- Query DB for review by PK
- Return 404 if not exist
- Return review detail if found

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[GET /reviews/{id}] --> B[Validate ID]
    B -- Invalid --> Z[422 Validation Error]
    B -- Valid --> C[Query DB]
    C -- Not found --> Y[404]
    C -- Found --> D[Return 200]
```
---
## 4) Database Impact
- Table: reviews (SELECT by PK)
---
## 5) Integrations & External Effects
None
---
## 6) Security
- None
---
## 7) Observability (Logging/Monitoring)
- Log errors/404s
---
## 8) Performance & Scalability
- Indexed PK
---
## 9) Edge Cases & Business Rules
- Error if not found
---
## 10) Testing
- Valid/invalid id, not found
- Example:
```bash
curl "https://api.example.com/api/v1/reviews/289"
```
---
## 11) Versioning & Deprecation
- v1
---
## 12) Changelog
- [2025-10-30] Initial version – ENGLISH
---
## 13) OpenAPI/Swagger Mapping
- Component: ReviewResource, ApiEnvelope
---
## 14) Completion Checklist
- [x] Endpoint clear
- [x] Request schema/validation
- [x] Response schema/error codes
- [x] Mermaid chart/logic
- [x] DB impact
- [x] Test/FE example
- [x] OpenAPI mapping
