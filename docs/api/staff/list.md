## API Name
Staff: List Staff (GET /api/v1/staff)

Purpose: Returns a paginated, optionally filtered list of spa staff (practitioners, therapists, etc.). Public endpoint.

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
- **Path**: /api/v1/staff
- **Auth**: None (public)
- **Rate limiting**: 60 req/minute
- **Caching**: N/A

#### Headers
| Name         | Required | Example          | Description        |
|--------------|----------|------------------|--------------------|
| Content-Type | No       | application/json | Request format     |

#### Query Params
| Name       | Type   | Required | Example         | Description                                   |
|------------|--------|----------|-----------------|------------------------------------------------|
| page       | int    | No       | 2               | Pagination page                               |
| per_page   | int    | No       | 10              | Items per page                                |
| sort       | string | No       | rating          | Which field to sort by (id, rating, years_of_experience) |
| direction  | string | No       | desc            | Sort order (asc or desc)                      |
| include    | string | No       | branch,services | Relations to include: user,branch,services    |
| position   | string | No       | therapist       | Filter by position string (partial LIKE)      |

#### Path Params
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
    {
      "id": 88,
      "name": "Nguyen A",
      "position": "Therapist",
      "branch": {"id":3, "name": "Branch 3"},
      "services": [ {"id":2,"name": "Facial"} ],
      "rating": 4.8,
      ...
    } ...
  ],
  "meta": { "pagination": { "page": 2, "per_page": 10, "total": 47 } }
}
```
#### Common Error Codes
| HTTP | Internal code    | When it happens         | Frontend handling |
|------|------------------|-------------------------|-------------------|
| 400  | VALIDATION_ERROR | Invalid query params    | Show error        |
| 500  | INTERNAL_ERROR   | Server error            | Retry/support     |
---
## 3) Flow Logic
- Parse and validate query parameters
- DB query staff paginated, filter by position
- Support relation includes, sorting, direction
- Return enveloped, paginated result

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[GET /staff Request] --> B[Validate/filter params]
    B --> C[Query staff table]
    C --> D[Load requested relations]
    D --> E[Paginate result]
    E --> F[Return 200 Success]
```
---
## 4) Database Impact
- Table: staff (SELECT, paginated)
---
## 5) Integrations & External Effects
None
---
## 6) Security
- Public
---
## 7) Observability (Logging/Monitoring)
- Log performance, unusual queries
---
## 8) Performance & Scalability
- Large list should paginate efficiently
---
## 9) Edge Cases & Business Rules
- Empty list for no staff/filters
---
## 10) Testing
- Paginated queries, sort dir, include relations
- Example:
```bash
curl "https://api.example.com/api/v1/staff?sort=rating&direction=desc&include=branch"
```
---
## 11) Versioning & Deprecation
- v1
---
## 12) Changelog
- [2025-10-30] Initial version – ENGLISH
---
## 13) OpenAPI/Swagger Mapping
- Component: StaffResource, ApiEnvelope
---
## 14) Completion Checklist
- [x] Endpoint clear
- [x] Request schema & validation
- [x] Response schema/error codes
- [x] Mermaid chart/logic
- [x] DB impact
- [x] Performance/scaling
- [x] Test/FE example
- [x] OpenAPI mapping
