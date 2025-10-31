## API Name
Service: Get Service by ID or Slug (GET /api/v1/services/{id})

Purpose: Retrieve full detail of a particular service by numeric ID or URL slug.

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
- **Path**: /api/v1/services/{id}
- **Auth**: None
- **Rate limiting**: 60 req/minute
- **Caching**: Server-side 15 minutes (per locale + id/slug); clients may use ETag/Cache-Control

#### Headers
| Name         | Required | Example            | Description        |
|--------------|----------|--------------------|--------------------|
| Content-Type | No       | application/json   | Request format     |

#### Path Params
| Name | Type         | Required | Example              | Description                     |
|------|--------------|----------|----------------------|---------------------------------|
| id   | int or slug  | Yes      | 120, premium-facial  | Service ID (number) or slug     |

#### Request Body Schema
N/A
---
## 2) Response
#### Standard error envelope
```json
{
  "success": false,
  "message": "Service not found",
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
    "id": 120,
    "category": {"id": 2, "name": "Facial", "slug": "facial"},
    "name": "Premium Facial",
    "slug": "premium-facial",
    "short_description": "Cleanse & rejuvenate",
    "description": "A deep cleansing ...",
    "price": 500000,
    "discounted_price": 450000,
    "final_price": 450000,
    "duration": 60,
    "is_featured": true,
    "image": "...",
    ...
  }
}
```
#### Common Error Codes
| HTTP | Internal code    | When it happens        | Frontend handling |
|------|------------------|------------------------|-------------------|
| 404  | NOT_FOUND        | No such service        | Show empty/error  |
| 500  | INTERNAL_ERROR   | Server error           | Retry/support     |
---
## 3) Flow Logic
- Validate input as id (number) or slug (string)
- If numeric, search by id; otherwise, by slug
- 404 if not found
- Return detail if found

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[GET /services/{id}] --> B{ID Numeric?}
    B -- Yes --> C[Query by ID]
    B -- No  --> D[Query by Slug]
    C & D -- Not Found --> X[404 Not Found]
    C & D -- Found --> E[Return 200 Success]
```
---
## 4) Database Impact
- Table: services (SELECT by PK or slug)
---
## 5) Integrations & External Effects
None
---
## 6) Security
- None (public)
---
## 7) Observability (Logging/Monitoring)
- Log 404/errors for monitoring
---
## 8) Performance & Scalability
- Fast index lookup by PK or slug
---
## 9) Edge Cases & Business Rules
- Only numeric or slug; error if not found
---
## 10) Testing
- Valid id, valid slug, not found cases
- Example:
```bash
curl "https://api.example.com/api/v1/services/120"
curl "https://api.example.com/api/v1/services/premium-facial"
```
---
## 11) Versioning & Deprecation
- v1
---
## 12) Changelog
- [2025-10-30] Initial version – ENGLISH
- [2025-10-30] Support both ID and slug
---
## 13) OpenAPI/Swagger Mapping
- Component: ServiceResource, ApiEnvelope
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
