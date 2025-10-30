# Payment Service - Repository Pattern Refactoring

## Overview
Refactored `PaymentService` to follow the repository pattern consistently used across the application, eliminating direct database model access.

## Changes Made

### 1. Created PaymentRepository Infrastructure

#### PaymentRepositoryInterface (`app/Repositories/Contracts/PaymentRepositoryInterface.php`)
```php
interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    public function getWithFilters(array $filters): LengthAwarePaginator;
    public function findByTransactionId(string $transactionId): ?Payment;
    public function getByBookingId(int $bookingId);
    public function getByUserId(int $userId);
}
```

#### PaymentRepository (`app/Repositories/Eloquent/PaymentRepository.php`)
- Extends `BaseRepository`
- Implements `PaymentRepositoryInterface`
- Supports filtering by: `user_id`, `status`, `payment_method`, `booking_id`
- Provides specialized queries for VNPay integration

### 2. Registered Repository Binding

**File:** `app/Providers/AppServiceProvider.php`

Added imports:
```php
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Eloquent\PaymentRepository;
```

Added binding in `register()`:
```php
$this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
```

### 3. Refactored PaymentService

#### Constructor Injection
**Before:**
```php
class PaymentService implements PaymentServiceInterface
{
    // No dependencies injected
}
```

**After:**
```php
class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected BookingRepositoryInterface $bookingRepository
    ) {
    }
}
```

#### list() Method
**Before:**
```php
public function list(Request $request): LengthAwarePaginator
{
    $query = Payment::query();
    if ($request->user()) {
        $query->whereHas('booking', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        });
    }
    // ... more query building
    return $query->latest('id')->paginate($perPage);
}
```

**After:**
```php
public function list(Request $request): LengthAwarePaginator
{
    $filters = [
        'per_page' => (int)($request->query('per_page', 15)),
        'user_id' => $request->user()?->id,
        'status' => $request->query('status'),
        'payment_method' => $request->query('payment_method'),
    ];
    
    return $this->paymentRepository->getWithFilters($filters);
}
```

#### vnpayCreate() Method
**Before:**
```php
$booking = Booking::findOrFail($bookingId);
// ...
$payment = Payment::create([...]);
```

**After:**
```php
$booking = $this->bookingRepository->find($bookingId);
if (!$booking) {
    throw new \Illuminate\Database\Eloquent\ModelNotFoundException(...);
}
// ...
$payment = $this->paymentRepository->create([...]);
```

#### vnpayReturn() Method
**Before:**
```php
$payment = Payment::where('transaction_id', $txnRef)->first();
// ...
$payment->update(['status' => ...]);
// ...
$booking->update([...]);
```

**After:**
```php
$payment = $this->paymentRepository->findByTransactionId($txnRef);
// ...
$this->paymentRepository->update($payment->id, ['status' => ...]);
$payment = $this->paymentRepository->find($payment->id); // Refresh
// ...
$this->bookingRepository->update($booking->id, [...]);
```

#### vnpayIpn() Method
**Before:**
```php
$payment = Payment::where('transaction_id', $txnRef)->first();
// ...
$payment->update(['status' => $status]);
```

**After:**
```php
$payment = $this->paymentRepository->findByTransactionId($txnRef);
// ...
$this->paymentRepository->update($payment->id, ['status' => $status]);
```

#### vnpayRefund() Method
**Before:**
```php
$payment = Payment::where('transaction_id', $transactionId)->firstOrFail();
$payment->update(['status' => 'refunded']);
```

**After:**
```php
$payment = $this->paymentRepository->findByTransactionId($transactionId);
if (!$payment) {
    throw new \Illuminate\Database\Eloquent\ModelNotFoundException(...);
}
$this->paymentRepository->update($payment->id, ['status' => 'refunded']);
$payment = $this->paymentRepository->find($payment->id); // Refresh
```

#### vnpayQuery() Method
**Before:**
```php
$payment = Payment::where('transaction_id', $transactionId)->firstOrFail();
```

**After:**
```php
$payment = $this->paymentRepository->findByTransactionId($transactionId);
if (!$payment) {
    throw new \Illuminate\Database\Eloquent\ModelNotFoundException(...);
}
```

## Benefits

### 1. **Architectural Consistency**
- PaymentService now follows the same repository pattern as all other services
- No direct model access in service layer

### 2. **Improved Testability**
- Dependencies are injected via constructor
- Can easily mock `PaymentRepositoryInterface` in unit tests
- No need to use database in tests

### 3. **Better Separation of Concerns**
- Service layer: Business logic (VNPay integration, validation, workflows)
- Repository layer: Data access (queries, filters, CRUD operations)

### 4. **Maintainability**
- Query logic centralized in repository
- Changes to data access don't affect service layer
- Easier to understand and modify

### 5. **Flexibility**
- Can swap implementations without changing service code
- Easy to add caching layer in repository
- Can implement different storage backends

## VNPay Integration Preserved

All VNPay payment gateway logic remains intact:
- ✅ Hash generation (`vnpHash()`)
- ✅ Base parameters (`vnpBaseParams()`)
- ✅ Payment URL generation
- ✅ Signature verification
- ✅ Return URL handling
- ✅ IPN webhook processing
- ✅ Refund simulation
- ✅ Transaction query

## Testing Recommendations

To create unit tests for PaymentService:

```php
// tests/Unit/Services/PaymentServiceTest.php
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Services\PaymentService;
use Mockery;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    private $paymentRepository;
    private $bookingRepository;
    private $paymentService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->paymentRepository = Mockery::mock(PaymentRepositoryInterface::class);
        $this->bookingRepository = Mockery::mock(BookingRepositoryInterface::class);
        
        $this->paymentService = new PaymentService(
            $this->paymentRepository,
            $this->bookingRepository
        );
    }
    
    // Test methods...
}
```

## Files Modified

1. ✅ `app/Repositories/Contracts/PaymentRepositoryInterface.php` (created)
2. ✅ `app/Repositories/Eloquent/PaymentRepository.php` (created)
3. ✅ `app/Providers/AppServiceProvider.php` (updated)
4. ✅ `app/Services/PaymentService.php` (refactored)

## No Breaking Changes

- Public API of `PaymentService` remains unchanged
- All method signatures are identical
- Controllers and routes require no modifications
- VNPay integration logic preserved exactly

## Conclusion

The refactoring successfully brings `PaymentService` in line with the application's architectural standards while maintaining all functionality and VNPay integration logic. The service is now more testable, maintainable, and consistent with other services in the codebase.
