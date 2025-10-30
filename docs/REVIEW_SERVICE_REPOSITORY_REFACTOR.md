# ReviewService Refactoring - Repository Pattern

## Tổng Quan

Refactor `ReviewService` để tất cả tương tác với database đều thông qua repositories thay vì trực tiếp query Model.

## Vấn Đề Trước Khi Refactor

ReviewService có 3 chỗ tương tác trực tiếp với database:

1. **Line 47**: `Booking::findOrFail()` - Query trực tiếp Booking model
2. **Line 50-52**: `Review::where()` - Query trực tiếp Review model để check duplicate
3. **Line 144**: `Review::with()` - Query trực tiếp Review model cho pending reviews

## Thay Đổi Chi Tiết

### 1. Thêm Methods vào ReviewRepositoryInterface

```php
/**
 * Check if user has already reviewed a booking.
 */
public function hasUserReviewedBooking(int $bookingId, int $userId): bool;

/**
 * Get pending reviews with pagination.
 */
public function getPending(int $perPage = 15): LengthAwarePaginator;
```

### 2. Implement Methods trong ReviewRepository

#### `hasUserReviewedBooking()`
```php
public function hasUserReviewedBooking(int $bookingId, int $userId): bool
{
    return $this->model
        ->where('booking_id', $bookingId)
        ->where('user_id', $userId)
        ->exists();
}
```

#### `getPending()`
```php
public function getPending(int $perPage = 15): LengthAwarePaginator
{
    return $this->model
        ->with(['user', 'service', 'staff', 'branch'])
        ->where('is_approved', false)
        ->latest('id')
        ->paginate($perPage);
}
```

### 3. Inject BookingRepository vào ReviewService

#### Before:
```php
class ReviewService implements ReviewServiceInterface
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository
    ) {}
}
```

#### After:
```php
class ReviewService implements ReviewServiceInterface
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository,
        private BookingRepositoryInterface $bookingRepository
    ) {}
}
```

### 4. Refactor `createFromBooking()` Method

#### Before (Direct Model Access):
```php
public function createFromBooking(array $reviewData, int $userId): Model
{
    // Direct Booking model query
    $booking = \App\Models\Booking::findOrFail($reviewData['booking_id']);
    
    // Direct Review model query
    $exists = Review::where('booking_id', $booking->id)
        ->where('user_id', $userId)
        ->exists();
        
    if ($exists) {
        throw new \Exception(__('reviews.duplicate_review'));
    }
    
    // ... rest of code
}
```

#### After (Repository Pattern):
```php
/**
 * @throws BusinessException
 */
public function createFromBooking(array $reviewData, int $userId): Model
{
    // Use BookingRepository
    $booking = $this->bookingRepository->getById($reviewData['booking_id']);
    
    if (!$booking) {
        throw new BusinessException(
            __('bookings.not_found'),
            'Booking Not Found',
            'BOOKING_NOT_FOUND',
            404
        );
    }
    
    // Use ReviewRepository method
    if ($this->reviewRepository->hasUserReviewedBooking($booking->id, $userId)) {
        throw new BusinessException(
            __('reviews.duplicate_review'),
            'Duplicate Review',
            'DUPLICATE_REVIEW',
            422
        );
    }
    
    // ... rest of code
}
```

### 5. Refactor `pending()` Method

#### Before (Direct Model Query):
```php
public function pending(Request $request): LengthAwarePaginator
{
    $perPage = (int) $request->query('per_page', 15);
    return Review::with(['user','service','staff','branch'])
        ->where('is_approved', false)
        ->latest('id')
        ->paginate($perPage);
}
```

#### After (Repository Pattern):
```php
public function pending(Request $request): LengthAwarePaginator
{
    $perPage = (int) $request->query('per_page', 15);
    return $this->reviewRepository->getPending($perPage);
}
```

### 6. Thêm Translations

Đã thêm translation cho `bookings.not_found`:

**vi/bookings.php:**
```php
'not_found' => 'Không tìm thấy đặt lịch',
```

**en/bookings.php:**
```php
'not_found' => 'Booking not found',
```

## Lợi Ích

### 1. **Consistent Architecture**
- ✅ Tất cả database access đều qua repositories
- ✅ Service layer không biết về database implementation details
- ✅ Tuân thủ Repository Pattern đúng cách

### 2. **Testability**
- ✅ Dễ dàng mock repositories trong unit tests
- ✅ Không cần setup database cho service tests
- ✅ Test service logic độc lập với database

### 3. **Maintainability**
- ✅ Database queries tập trung ở một nơi (repositories)
- ✅ Dễ dàng thay đổi query logic
- ✅ Reusable methods cho các services khác

### 4. **Better Error Handling**
- ✅ Thay `Exception` bằng `BusinessException`
- ✅ Proper HTTP status codes (404, 422)
- ✅ Consistent error response format
- ✅ Multi-language error messages

### 5. **Separation of Concerns**
- ✅ Repository: Data access layer
- ✅ Service: Business logic layer
- ✅ Controller: HTTP layer
- ✅ Clear responsibilities

## Code Quality Improvements

### Before:
```php
// ❌ Service biết về database structure
$exists = Review::where('booking_id', $booking->id)
    ->where('user_id', $userId)
    ->exists();

// ❌ Direct model query
$booking = \App\Models\Booking::findOrFail($reviewData['booking_id']);

// ❌ Generic exception
throw new \Exception(__('reviews.duplicate_review'));
```

### After:
```php
// ✅ Service chỉ gọi repository methods
if ($this->reviewRepository->hasUserReviewedBooking($booking->id, $userId)) {
    // Handle duplicate
}

// ✅ Use injected repository
$booking = $this->bookingRepository->getById($reviewData['booking_id']);

// ✅ Proper exception with error codes
throw new BusinessException(
    __('reviews.duplicate_review'),
    'Duplicate Review',
    'DUPLICATE_REVIEW',
    422
);
```

## Testing Impact

Với refactoring này, unit tests cho ReviewService sẽ:

```php
class ReviewServiceTest extends TestCase
{
    public function test_create_from_booking_throws_exception_when_booking_not_found()
    {
        // Mock repository
        $this->bookingRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);
        
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('Booking not found');
        
        $this->reviewService->createFromBooking(['booking_id' => 999], 1);
    }
    
    public function test_create_from_booking_throws_exception_when_duplicate_review()
    {
        $booking = Mockery::mock(Booking::class)->makePartial();
        
        $this->bookingRepository
            ->shouldReceive('getById')
            ->andReturn($booking);
        
        // Mock duplicate check
        $this->reviewRepository
            ->shouldReceive('hasUserReviewedBooking')
            ->once()
            ->with($booking->id, 1)
            ->andReturn(true);
        
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('You have already reviewed this booking');
        
        $this->reviewService->createFromBooking(['booking_id' => 1], 1);
    }
}
```

## Files Changed

1. ✅ `app/Repositories/Contracts/ReviewRepositoryInterface.php` - Added 2 methods
2. ✅ `app/Repositories/Eloquent/ReviewRepository.php` - Implemented 2 methods
3. ✅ `app/Services/ReviewService.php` - Refactored to use repositories
4. ✅ `resources/lang/vi/bookings.php` - Added translation
5. ✅ `resources/lang/en/bookings.php` - Added translation

## Summary

| Aspect | Before | After |
|--------|--------|-------|
| Direct Model Queries | 3 places | 0 places |
| Repository Usage | Partial | Complete |
| Error Handling | Generic Exception | BusinessException |
| Testability | Hard to test | Easy to mock |
| Maintainability | Scattered logic | Centralized |

## Next Steps

Similar refactoring có thể áp dụng cho:
- [ ] ReviewService unit tests
- [ ] Các services khác có direct model queries
- [ ] Controller tests với mocked services

**Status**: ✅ Complete - All database interactions now use repositories
