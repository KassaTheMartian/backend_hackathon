## Demo API

Base path: `/api/v1`

### Resource
- Entity: `Demo`
- Representation (DemoResource):
  - `id` (integer)
  - `title` (string)
  - `description` (string|null)
  - `is_active` (boolean)
  - `created_at` (ISO 8601 string|null)
  - `updated_at` (ISO 8601 string|null)

### Envelope
All responses are wrapped in a standard envelope:

```json
{
  "success": true,
  "message": "...",
  "data": {},
  "error": null,
  "meta": {},
  "trace_id": "uuid",
  "timestamp": "2025-01-01T00:00:00.000Z"
}
```

On errors, `success=false`, `data=null`, and `error` contains `{ type, code, details? }`.

---

### List Demos
- Method: GET
- Path: `/api/v1/demos`
- Auth: Not required
- Query params:
  - `page` (integer)
  - `per_page` (integer)

Response 200
```json
{
  "success": true,
  "message": "Demos retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Sample",
      "description": "...",
      "is_active": true,
      "created_at": "2025-10-28T07:00:00.000Z",
      "updated_at": "2025-10-28T07:00:00.000Z"
    }
  ],
  "error": null,
  "meta": {
    "page": 1,
    "page_size": 15,
    "total_count": 1,
    "total_pages": 1,
    "has_next_page": false,
    "has_previous_page": false
  },
  "trace_id": "...",
  "timestamp": "..."
}
```

---

### Create Demo
- Method: POST
- Path: `/api/v1/demos`
- Auth: Required (`Authorization: Bearer <token>` via Sanctum)
- Request body (JSON):
  - `title` (string, required, max 255)
  - `description` (string, optional)
  - `is_active` (boolean, optional)

Response 201
```json
{
  "success": true,
  "message": "Demo created successfully",
  "data": {
    "id": 1,
    "title": "New Demo",
    "description": null,
    "is_active": true,
    "created_at": "2025-10-28T07:00:00.000Z",
    "updated_at": "2025-10-28T07:00:00.000Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "...",
  "timestamp": "..."
}
```

Validation Error 400
```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "error": {
    "type": "ValidationError",
    "code": "VALIDATION_FAILED",
    "details": {
      "title": ["The title field is required."]
    }
  },
  "meta": null,
  "trace_id": "...",
  "timestamp": "..."
}
```

Unauthorized 401
```json
{
  "success": false,
  "message": "Unauthorized",
  "data": null,
  "error": {"type": "Unauthorized", "code": "UNAUTHORIZED"},
  "meta": null,
  "trace_id": "...",
  "timestamp": "..."
}
```

---

### Get Demo by ID
- Method: GET
- Path: `/api/v1/demos/{id}`
- Auth: Not required

Response 200
```json
{
  "success": true,
  "message": "Demo retrieved successfully",
  "data": {
    "id": 1,
    "title": "Sample",
    "description": "...",
    "is_active": true,
    "created_at": "2025-10-28T07:00:00.000Z",
    "updated_at": "2025-10-28T07:00:00.000Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "...",
  "timestamp": "..."
}
```

Not Found 404
```json
{
  "success": false,
  "message": "Resource not found",
  "data": null,
  "error": {"type": "NotFound", "code": "RESOURCE_NOT_FOUND"},
  "meta": null,
  "trace_id": "...",
  "timestamp": "..."
}
```

---

### Update Demo
- Method: PUT
- Path: `/api/v1/demos/{id}`
- Auth: Required (`Authorization: Bearer <token>` via Sanctum)
- Request body (JSON):
  - `title` (string, optional, max 255)
  - `description` (string, optional)
  - `is_active` (boolean, optional)

Response 200
```json
{
  "success": true,
  "message": "Demo updated successfully",
  "data": {
    "id": 1,
    "title": "Updated",
    "description": "...",
    "is_active": true,
    "created_at": "2025-10-28T07:00:00.000Z",
    "updated_at": "2025-10-28T07:10:00.000Z"
  },
  "error": null,
  "meta": null,
  "trace_id": "...",
  "timestamp": "..."
}
```

Not Found 404 and Unauthorized 401 follow the same shapes as above.

---

### Delete Demo
- Method: DELETE
- Path: `/api/v1/demos/{id}`
- Auth: Required (`Authorization: Bearer <token>` via Sanctum)

Response 204
```json
{
  "success": true,
  "message": "Demo deleted successfully",
  "data": null,
  "error": null,
  "meta": null,
  "trace_id": "...",
  "timestamp": "..."
}
```

If the resource does not exist, 404 as above. If not authenticated, 401 as above.


