## API Name
Booking: Update Booking (PUT /api/v1/bookings/{id})

Purpose: Allows authenticated users to update data of an existing booking they own (date/time, service, notes, possibly branch/staff, etc. within allowed time window).

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | frontend dev | QA | customer
- **Related epic/ticket**: [TBD]

---
## 1) Endpoint
- **Method**: PUT
- **Base URL**: https://api.example.com
- **Path**: /api/v1/bookings/{id}
- **Auth**: Bearer token / Sanctum required
- **Rate limiting**: 60 req/minute

#### Headers
| Name           | Required | Example               | Description             |
|----------------|----------|----------------------|-------------------------|
| Authorization  | Yes      | Bearer <token>       | User authentication     |
| Content-Type   | Yes      | application/json     | Request format          |

#### Path Params
| Name | Type  | Required | Example | Description         |
|------|-------|----------|---------|---------------------|
| id   | int   | Yes      | 252     | Booking ID          |

#### Request Body Schema
Only fields to be updated need to be provided; typically:
```json
{
  "service_id": 21,
  "staff_id": 52,
  "booking_date": "2025-11-10",
  "booking_time": "10:30",
  "notes": "Update notes"
}
```
- All fields optional. Service/staff/branch IDs must exist if sent. New booking_date/time must be in future; slot must be available.

#### Query Params
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
    "id": 252,
    ...
    "booking_date": "2025-11-10",
    ...
    "status": "pending",
    ...
  }
}
```
#### Common Error Codes
| HTTP | Internal code      | When it happens                  | Frontend handling            |
|------|--------------------|----------------------------------|------------------------------|
| 401  | UNAUTHORIZED       | Not logged in/out of session      | Prompt login                 |
| 404  | NOT_FOUND          | Booking does not exist            | Show not found error         |
| 422  | VALIDATION_ERROR   | Business/validation logic fail    | Highlight error, explain     |
| 409  | TIME_UNAVAILABLE   | New slot unavailable              | Prompt another slot/time     |

---
## 3) Flow Logic
- Authorize user for the booking
- Validate new input (date/time, staff/service/branch IDs)
- Ensure time slot is available
- Update only sent fields
- Return updated booking
---
## 4) Database Impact
- Table: bookings (UPDATE)
---
## 5) Integrations & External Effects
- Email/SMS update notification possible (future)
---
## 6) Security
- Only owner or admin can update booking
---
## 7) Observability (Logging/Monitoring)
- Log failures, suspicious frequency, slot collisions
---
## 8) Performance & Scalability
- Normal
---
## 9) Edge Cases & Business Rules
- Cannot reschedule after cutoff/time passed
- Only updatable if not cancelled/completed
---
## 10) Testing
- Valid change, invalid fields, time clash, not found
- Example:
```bash
curl -X PUT "https://api.example.com/api/v1/bookings/252" -H "Authorization: Bearer <token>" -H "Content-Type: application/json" -d '{"service_id":21,"booking_date":"2025-11-10","booking_time":"10:30"}'
```
---
## 11) Versioning & Deprecation
- v1
---
## 12) Changelog
- [2025-10-30] Initial ENGLISH version
---
## 13) OpenAPI/Swagger Mapping
- Component: BookingResource, ApiEnvelope
---
## 14) Completion Checklist
- [x] Endpoint clear
- [x] Request schema & validation
- [x] Response schema & error codes
- [x] Flow logic document
- [x] DB impact
- [x] Security
- [x] Logging/metrics
- [x] Performance note
- [x] Test/FE example
- [x] OpenAPI mapping
