## API Name
Post: Get Post by ID or Slug (GET /api/v1/posts/{id})

Purpose: Fetches the detail for a post given either its numeric ID or friendly URL slug. Also increments its view count.

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
- **Path**: /api/v1/posts/{id}
- **Auth**: None
- **Rate limiting**: 60 req/minute
- **Caching**: Server-side 15 minutes (per locale + id/slug)

#### Headers
| Name         | Required | Example            | Description        |
|--------------|----------|--------------------|--------------------|
| Content-Type | No       | application/json   | Request format     |

#### Path Params
| Name | Type         | Required | Example             | Description               |
|------|--------------|----------|---------------------|---------------------------|
| id   | int or slug  | Yes      | 23, how-to-book     | Post ID (number) or slug  |

#### Request Body Schema
N/A
---
## 2) Response
#### Standard error envelope
```json
{
  "success": false,
  "message": "Post not found",
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
    "id": 23,
    "category": {"id": 6, "name": "News", "slug": "news"},
    "title": "How to book online",
    "slug": "how-to-book-online",
    "excerpt": "...",
    "content": "...",
    "is_featured": false,
    "views_count": 13,
    ...
  }
}
```
#### Common Error Codes
| HTTP | Internal code    | When it happens    | Frontend handling |
|------|------------------|--------------------|-------------------|
| 404  | NOT_FOUND        | No such post       | Show empty/error  |
| 500  | INTERNAL_ERROR   | Server error       | Retry/support     |
---
## 3) Flow Logic
- Validate ID/slug
- Determine search mode (int: id, string: slug)
- Lookup in posts DB
- 404 if not found
- If found: increment view count, return details

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[GET /posts/{id}] --> B{ID Numeric?}
    B -- Yes --> C[Find by ID]
    B -- No  --> D[Find by slug]
    C & D -- Not found --> Y[404]
    C & D -- Found --> E[Increment view count]
    E --> F[Return 200]
```
---
## 4) Database Impact
- Table: posts (SELECT by PK or slug, increment view)
---
## 5) Integrations & External Effects
None
---
## 6) Security
- None
---
## 7) Observability (Logging/Monitoring)
- Log view increments, 404
---
## 8) Performance & Scalability
- Efficient index on PK and slug
---
## 9) Edge Cases & Business Rules
- Error if not found, ID or slug allowed
---
## 10) Testing
- Valid id, slug, non-existent, inc view
- Example:
```bash
curl "https://api.example.com/api/v1/posts/how-to-book"
curl "https://api.example.com/api/v1/posts/23"
```
---
## 11) Versioning & Deprecation
- v1
---
## 12) Changelog
- [2025-10-30] Initial version – ENGLISH
---
## 13) OpenAPI/Swagger Mapping
- Component: PostResource, ApiEnvelope
---
## 14) Completion Checklist
- [x] Endpoint clear
- [x] Request schema/validation
- [x] Response schema/error codes
- [x] Mermaid chart/logic
- [x] DB impact
- [x] Security
- [x] Logging/metrics
- [x] Test/FE example
- [x] OpenAPI mapping
