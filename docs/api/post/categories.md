## API Name
Post: List Post Categories (GET /api/v1/post-categories)

Purpose: Returns all available categories for posts (e.g. news, tips, reviews) for filtering in UI.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: frontend dev | backend dev | QA | customer
- **Related epic/ticket**: [TBD]
---
## 1) Endpoint
- **Method**: GET
- **Base URL**: https://api.example.com
- **Path**: /api/v1/post-categories
- **Auth**: None
- **Rate limiting**: 60 req/minute

#### Headers
| Name         | Required | Example            | Description        |
|--------------|----------|--------------------|--------------------|
| Content-Type | No       | application/json   | Request format     |

#### Path Params
N/A
#### Query Params
N/A
#### Request Body Schema
N/A
---
## 2) Response
#### Standard error envelope
```json
{
  "success": false,
  "message": "Short error description",
  "code": "ERROR_CODE",
  "errors": {},
  "trace_id": "uuid"
}
```
#### 200 Success Example
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "News", "slug": "news" },
    { "id": 2, "name": "Review", "slug": "review" }
  ]
}
```
#### Common Error Codes
| HTTP | Internal code    | When it happens      | Frontend handling |
|------|------------------|----------------------|-------------------|
| 500  | INTERNAL_ERROR   | Server error         | Retry/support     |
---
## 3) Flow Logic
- Fetch all categories
- Return as array

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[Request Categories] --> B[Query post_categories]
    B --> C[Return 200 Success]
```
---
## 4) Database Impact
- Table: post_categories (SELECT ALL)
---
## 5) Integrations & External Effects
None
---
## 6) Security
- None
---
## 7) Observability (Logging/Monitoring)
- Log errors, request count
---
## 8) Performance & Scalability
- Fast, can cache
---
## 9) Edge Cases & Business Rules
- Empty result if none exist
---
## 10) Testing
- None/some/many categories
- Example:
```bash
curl "https://api.example.com/api/v1/post-categories"
```
---
## 11) Versioning & Deprecation
- v1
---
## 12) Changelog
- [2025-10-30] Initial version – ENGLISH
---
## 13) OpenAPI/Swagger Mapping
- Component: CategoryList, ApiEnvelope
---
## 14) Completion Checklist
- [x] Endpoint clear
- [x] Response schema/error codes
- [x] Mermaid chart/logic
- [x] DB impact
- [x] Logging/metrics
- [x] Test/FE example
- [x] OpenAPI mapping
