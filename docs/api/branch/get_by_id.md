## API Name
Branch: Get Branch Detail by ID or Slug (GET /api/v1/branches/{id})

Purpose: Returns detail info of a specific branch including services. Accepts numeric ID or URL slug as identifier.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA | customer
- **Related epic/ticket**: [TBD]

---
## 1) Endpoint
- **Method**: GET
- **Base URL**: https://api.example.com
- **Path**: /api/v1/branches/{id}
- **Environment**: dev | staging | prod
- **Auth**: None
- **Rate limiting**: 60 req/minute
- **Caching**: [ETag if implemented]

#### Headers
| Name         | Required | Example            | Description         |
|--------------|----------|--------------------|---------------------|
| Content-Type | Yes      | application/json   | Request format      |

#### Path Params
| Name | Type         | Required | Example                         | Description                |
|------|--------------|----------|----------------------------------|----------------------------|
| id   | int or slug  | Yes      | 1, spa-beauty-center-quan-1      | Branch ID or URL slug      |

#### Query Params
N/A

#### Request Body Schema
N/A

---
## 2) Response
#### Error Envelope (standard)
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
  "data": {
      "id": 1,
      "name": {"en": "Spa ..."},
      "slug": "spa-beauty-center-quan-1",
      "address": {"en": "123 Main St..."},
      "phone": "028 3829 1234",
      "email": "branch1@example.com",
      "latitude": 10.7769,
      "longitude": 106.7009,
      "opening_hours": {"monday": ["09:00", "20:00"]},
      "images": ["/storage/branches/b1.jpg"],
      "description": {"en": "Main branch ..."},
      "amenities": ["WiFi", "Parking"],
      "is_active": true,
      "display_order": 1,
      "created_at": "2025-10-30T12:34:56Z",
      "updated_at": "2025-10-30T12:34:56Z",
      "services": [
        {
         "id": 1,
         "name": "Facial Skin Care",
         "price": 500000,
         "discounted_price": 450000,
         "final_price": 450000,
         "duration": 60,
         "is_available": true,
         "custom_price": null
        }
      ]
    }
}
```
#### Common Error Codes
| HTTP | Internal code      | When it happens                  | Frontend handling     |
|------|--------------------|----------------------------------|----------------------|
| 404  | NOT_FOUND          | Branch with provided id/slug not found | Show empty state |
| 500  | INTERNAL_ERROR     | Server error                     | Retry / support      |

---
## 3) Flow Logic
- Receive request, lookup by id (number) or slug (string)
- Return 404 if not found
- Return branch detail, services eager loaded

**Mermaid Flowchart:**
```mermaid
flowchart TD
    A[Request with ID or Slug] --> B[Find branch (by id or slug)]
    B -- Not found --> Z[404 Not Found]
    B -- Found --> C[Load related services]
    C --> D[Return 200 Success]
```
---
## 4) Database Impact
- Table: branches (SELECT by PK or slug)
- Related: branch_services, services (eager load)
- Index: PK, slug
- Transactions: no
---
## 5) Integrations & External Effects
None
---
## 6) Security
- Public, no authentication required
---
## 7) Observability (Logging/Monitoring)
- Error and query logging as standard
---
## 8) Performance & Scalability
- Eager load (`services`) recommended for efficiency
---
## 9) Edge Cases & Business Rules
- Non-existent slug or id returns 404
---
## 10) Testing
- Valid id, valid slug, id not found, slug not found
- Example cURL:
```bash
curl "https://api.example.com/api/v1/branches/1"
curl "https://api.example.com/api/v1/branches/spa-beauty-center-quan-1"
```
---
## 11) Versioning & Deprecation
- v1
---
## 12) Changelog
- [2025-10-30] Initial version – ENGLISH
---
## 13) OpenAPI/Swagger Mapping
- Component: BranchResource, ApiEnvelope
---
## 14) Completion Checklist
- [x] Endpoint clear
- [x] Request schema & validation
- [x] Response schema & error codes
- [x] Flow logic complete
- [x] DB impact
- [x] Security
- [x] Logging/metrics
- [x] Performance note
- [x] Test/FE example
- [x] OpenAPI mapping
