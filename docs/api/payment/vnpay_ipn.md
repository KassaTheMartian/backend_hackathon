## API Name
Payment: VNPay Server IPN (POST /api/v1/payments/vnpay/ipn)

Purpose: VNPay payment gateway server POSTs here to notify our backend of payment result asynchronously. Ensures status is correctly set even if user closes browser. Mission-critical for booking/payment reliability.

### General Information
- **Owner**: backend
- **Version**: v1
- **Status**: ready
- **Audience**: backend dev | devops | QA
---
## 1) Endpoint
- **Method**: POST
- **Base URL**: https://api.example.com
- **Path**: /api/v1/payments/vnpay/ipn
- **Auth**: None (IP whitelist, server only)

#### Headers
| Name         | Required | Example            | Description     |
|--------------|----------|--------------------|-----------------|
| Content-Type | Yes      | application/x-www-form-urlencoded | posted data  |

#### Body Schema
VNPay POST fields (x-www-form-urlencoded):
- vnp_TmnCode, vnp_Amount, vnp_TxnRef, vnp_ResponseCode, vnp_SecureHash, ...

---
## 2) Response
#### 200 Example
```
"OK"
```
#### Common Error Response
```
"INVALID CHECKSUM" or appropriate error
```
---
## 3) Flow Logic
- Read POST fields
- Validate secure hash
- Find payment/booking
- Idempotently apply/update status
- Log

**Mermaid Flowchart:**
```mermaid
flowchart TD
  A[POST /payments/vnpay/ipn] --> B[Validate signature]
  B -- Bad --> X["INVALID CHECKSUM"]
  B -- Good --> C[Find booking/payment]
  C -- Not found --> Y[Error/noop]
  C -- Found --> D[Apply update]
  D --> E[Log]
  E --> F[Return "OK"]
```
---
## 4) Database Impact
- payments: status update; bookings
---
## 5) Integrations & External Effects
- Only VNPay server
---
## 6) Security
- Signature, IP whitelist, logging
---
## 7) Observability (Logging/Monitoring)
- Required
---
## 8) Performance & Scalability
- High QPS capable
---
## 9) Edge Cases & Business Rules
- Out-of-order callback, retry, noise
---
## 10) Testing
- Simulated IPN from VNPay
---
## 11) Versioning & Deprecation
v1
---
## 12) Changelog
2025-10-30 – Initial
---
## 13) OpenAPI/Swagger Mapping
PaymentResource
---
## 14) Completion Checklist
[x] Coverage of all POST data, edge/abuse, idempotency
