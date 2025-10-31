## API Name
Post: List Post Tags (GET /api/v1/post-tags)

Purpose: Returns the list of tags applied to posts (for search/filtering or tag cloud display).

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
- **Path**: /api/v1/post-tags
- **Auth**: None
- **Rate limiting**: 60 req/minute
 - **Caching**: Server-side 60 minutes

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
    { "id": 1, "name": "booking", "slug": "booking" },
    { "id": 2, "name": "review", "slug": "review" }
  ]
}
```
#### Common Error Codes
| HTTP | Internal code    | When it happens      | Frontend handling |
|------|------------------|----------------------|-------------------|
| 500  | INTERNAL_ERROR   | Server error         | Retry/support     |
---
## 3) Flow Logic
- Fetch all tags
- Return as array

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[Request Tags] --> B[Query post_tags]
    B --> C[Return 200 Success]
```
---
## 4) Database Impact
- Table: post_tags (SELECT ALL)
---
## 5) Integrations & External Effects
None
---
## 6) Security
- None
---
## 7) Observability (Logging/Monitoring)
- Log errors, volume
---
## 8) Performance & Scalability
- Fast, can cache
---
## 9) Edge Cases & Business Rules
- Empty list if no tags
---
## 10) Testing
- None/some/many tags
- Example:
```bash
curl "https://api.example.com/api/v1/post-tags"
```
---
## 11) Versioning & Deprecation
- v1
---
## 12) Changelog
- [2025-10-30] Initial version – ENGLISH
---
## 13) OpenAPI/Swagger Mapping
- Component: TagList, ApiEnvelope
---
## 14) Completion Checklist
- [x] Endpoint clear
- [x] Response schema/error codes
- [x] Mermaid chart/logic
- [x] DB impact
- [x] Logging/metrics
- [x] Test/FE example
- [x] OpenAPI mapping
