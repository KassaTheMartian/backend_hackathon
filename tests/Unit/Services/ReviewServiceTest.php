<?php

namespace Tests\Unit\Services;

use App\Data\Review\ReviewData;
use App\Exceptions\BusinessException;
use App\Models\Booking;
use App\Models\Review;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Services\ReviewService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class ReviewServiceTest extends TestCase
{
    private $reviewRepository;
    private $bookingRepository;
    private $reviewService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reviewRepository = Mockery::mock(ReviewRepositoryInterface::class);
        $this->bookingRepository = Mockery::mock(BookingRepositoryInterface::class);

        $this->reviewService = new ReviewService(
            $this->reviewRepository,
            $this->bookingRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ========== list() Tests ==========

    public function test_list_returns_paginated_reviews(): void
    {
        $request = Request::create('/reviews', 'GET', [
            'service_id' => 1,
            'rating' => 5,
            'per_page' => 20,
        ]);

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->reviewRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with(Mockery::on(function ($filters) {
                return $filters['service_id'] === 1
                    && $filters['rating'] === 5
                    && $filters['per_page'] === 20;
            }))
            ->andReturn($paginator);

        $result = $this->reviewService->list($request);

        $this->assertSame($paginator, $result);
    }

    public function test_list_uses_default_per_page(): void
    {
        $request = Request::create('/reviews', 'GET');

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->reviewRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with(Mockery::on(function ($filters) {
                return $filters['per_page'] === 15;
            }))
            ->andReturn($paginator);

        $result = $this->reviewService->list($request);

        $this->assertSame($paginator, $result);
    }

    // ========== create() Tests ==========

    public function test_create_creates_review_from_dto(): void
    {
        $data = [
            'booking_id' => 1,
            'user_id' => 1,
            'service_id' => 1,
            'rating' => 5,
            'comment' => 'Great service!',
        ];

        $dto = ReviewData::from($data);

        $review = Mockery::mock(Review::class)->makePartial();
        $review->id = 1;

        $this->reviewRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return is_array($arg)
                    && $arg['user_id'] === 1
                    && $arg['service_id'] === 1
                    && $arg['rating'] === 5
                    && $arg['comment'] === 'Great service!';
            }))
            ->andReturn($review);

        $result = $this->reviewService->create($dto);

        $this->assertSame($review, $result);
        $this->assertEquals(1, $result->id);
    }

    // ========== createFromBooking() Tests ==========

    public function test_create_from_booking_throws_exception_when_booking_not_found(): void
    {
        $reviewData = ['booking_id' => 999];

        $this->bookingRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('Booking not found');

        $this->reviewService->createFromBooking($reviewData, 1);
    }

    public function test_create_from_booking_throws_exception_when_duplicate_review(): void
    {
        $reviewData = ['booking_id' => 1, 'rating' => 5, 'comment' => 'Good'];

        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->id = 1;
        $booking->service_id = 10;
        $booking->staff_id = 5;
        $booking->branch_id = 2;

        $this->bookingRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($booking);

        $this->reviewRepository
            ->shouldReceive('hasUserReviewedBooking')
            ->once()
            ->with(1, 1)
            ->andReturn(true);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('You have already reviewed this booking');

        $this->reviewService->createFromBooking($reviewData, 1);
    }

    public function test_create_from_booking_creates_review_successfully(): void
    {
        $reviewData = [
            'booking_id' => 1,
            'rating' => 5,
            'comment' => 'Excellent service!',
        ];

        $booking = Mockery::mock(Booking::class)->makePartial();
        $booking->id = 1;
        $booking->service_id = 10;
        $booking->staff_id = 5;
        $booking->branch_id = 2;

        $review = Mockery::mock(Review::class)->makePartial();
        $review->id = 100;

        $this->bookingRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($booking);

        $this->reviewRepository
            ->shouldReceive('hasUserReviewedBooking')
            ->once()
            ->with(1, 1)
            ->andReturn(false);

        $this->reviewRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['user_id'] === 1
                    && $data['service_id'] === 10
                    && $data['staff_id'] === 5
                    && $data['branch_id'] === 2
                    && $data['rating'] === 5
                    && $data['comment'] === 'Excellent service!';
            }))
            ->andReturn($review);

        $result = $this->reviewService->createFromBooking($reviewData, 1);

        $this->assertSame($review, $result);
        $this->assertEquals(100, $result->id);
    }

    // ========== find() Tests ==========

    public function test_find_returns_review(): void
    {
        $review = Mockery::mock(Review::class)->makePartial();
        $review->id = 1;

        $this->reviewRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($review);

        $result = $this->reviewService->find(1);

        $this->assertSame($review, $result);
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $this->reviewRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->reviewService->find(999);

        $this->assertNull($result);
    }

    // ========== getReviews() Tests ==========

    public function test_get_reviews_returns_paginated_reviews(): void
    {
        $filters = ['service_id' => 1, 'rating' => 5];
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->reviewRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($paginator);

        $result = $this->reviewService->getReviews($filters);

        $this->assertSame($paginator, $result);
    }

    public function test_get_reviews_with_empty_filters(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->reviewRepository
            ->shouldReceive('getWithFilters')
            ->once()
            ->with([])
            ->andReturn($paginator);

        $result = $this->reviewService->getReviews();

        $this->assertSame($paginator, $result);
    }

    // ========== getReviewById() Tests ==========

    public function test_get_review_by_id_returns_review(): void
    {
        $review = Mockery::mock(Review::class)->makePartial();
        $review->id = 1;

        $this->reviewRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($review);

        $result = $this->reviewService->getReviewById(1);

        $this->assertSame($review, $result);
    }

    // ========== getReviewWithDetails() Tests ==========

    public function test_get_review_with_details_loads_relationships(): void
    {
        $review = Mockery::mock(Review::class)->makePartial();
        $review->id = 1;

        $review->shouldReceive('load')
            ->once()
            ->with(['user', 'service', 'booking'])
            ->andReturnSelf();

        $result = $this->reviewService->getReviewWithDetails($review);

        $this->assertSame($review, $result);
    }

    // ========== createReview() Tests ==========

    public function test_create_review_creates_new_review(): void
    {
        $data = [
            'user_id' => 1,
            'service_id' => 1,
            'rating' => 4,
            'comment' => 'Good',
        ];

        $review = Mockery::mock(Review::class)->makePartial();

        $this->reviewRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($review);

        $result = $this->reviewService->createReview($data);

        $this->assertSame($review, $result);
    }

    // ========== updateReview() Tests ==========

    public function test_update_review_updates_existing_review(): void
    {
        $data = ['rating' => 5, 'comment' => 'Updated comment'];

        $review = Mockery::mock(Review::class)->makePartial();
        $review->rating = 5;

        $this->reviewRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($review);

        $result = $this->reviewService->updateReview(1, $data);

        $this->assertSame($review, $result);
        $this->assertEquals(5, $result->rating);
    }

    public function test_update_review_returns_null_when_not_found(): void
    {
        $data = ['rating' => 5];

        $this->reviewRepository
            ->shouldReceive('update')
            ->once()
            ->with(999, $data)
            ->andReturn(null);

        $result = $this->reviewService->updateReview(999, $data);

        $this->assertNull($result);
    }

    // ========== deleteReview() Tests ==========

    public function test_delete_review_deletes_successfully(): void
    {
        $this->reviewRepository
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->reviewService->deleteReview(1);

        $this->assertTrue($result);
    }

    public function test_delete_review_returns_false_when_not_found(): void
    {
        $this->reviewRepository
            ->shouldReceive('delete')
            ->once()
            ->with(999)
            ->andReturn(false);

        $result = $this->reviewService->deleteReview(999);

        $this->assertFalse($result);
    }

    // ========== approveReview() Tests ==========

    public function test_approve_review_approves_successfully(): void
    {
        $review = Mockery::mock(Review::class)->makePartial();
        $review->is_approved = true;

        $this->reviewRepository
            ->shouldReceive('approve')
            ->once()
            ->with($review)
            ->andReturn($review);

        $result = $this->reviewService->approveReview($review);

        $this->assertSame($review, $result);
        $this->assertTrue($result->is_approved);
    }

    // ========== rejectReview() Tests ==========

    public function test_reject_review_rejects_with_reason(): void
    {
        $review = Mockery::mock(Review::class)->makePartial();
        $review->is_approved = false;

        $this->reviewRepository
            ->shouldReceive('reject')
            ->once()
            ->with($review, 'Inappropriate content')
            ->andReturn($review);

        $result = $this->reviewService->rejectReview($review, 'Inappropriate content');

        $this->assertSame($review, $result);
        $this->assertFalse($result->is_approved);
    }

    // ========== pending() Tests ==========

    public function test_pending_returns_pending_reviews(): void
    {
        $request = Request::create('/reviews/pending', 'GET', ['per_page' => 20]);

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->reviewRepository
            ->shouldReceive('getPending')
            ->once()
            ->with(20)
            ->andReturn($paginator);

        $result = $this->reviewService->pending($request);

        $this->assertSame($paginator, $result);
    }

    public function test_pending_uses_default_per_page(): void
    {
        $request = Request::create('/reviews/pending', 'GET');

        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->reviewRepository
            ->shouldReceive('getPending')
            ->once()
            ->with(15)
            ->andReturn($paginator);

        $result = $this->reviewService->pending($request);

        $this->assertSame($paginator, $result);
    }

    // ========== respondToReview() Tests ==========

    public function test_respond_to_review_saves_response(): void
    {
        Auth::shouldReceive('id')->once()->andReturn(1);

        $review = Mockery::mock(Review::class)->makePartial();
        $review->id = 1;

        $review->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['admin_response'] === 'Thank you for your feedback'
                    && $data['responded_by'] === 1
                    && isset($data['responded_at']);
            }))
            ->andReturnTrue();

        $review->shouldReceive('fresh')
            ->once()
            ->andReturnSelf();

        $result = $this->reviewService->respondToReview($review, 'Thank you for your feedback');

        $this->assertSame($review, $result);
    }

    // ========== getReviewStats() Tests ==========

    public function test_get_review_stats_returns_statistics(): void
    {
        $filters = ['date_from' => '2025-01-01'];

        $stats = [
            'total_reviews' => 100,
            'approved_reviews' => 80,
            'pending_reviews' => 20,
            'average_rating' => 4.5,
        ];

        $this->reviewRepository
            ->shouldReceive('getStats')
            ->once()
            ->with($filters)
            ->andReturn($stats);

        $result = $this->reviewService->getReviewStats($filters);

        $this->assertIsArray($result);
        $this->assertEquals(100, $result['total_reviews']);
        $this->assertEquals(80, $result['approved_reviews']);
        $this->assertEquals(4.5, $result['average_rating']);
    }

    public function test_get_review_stats_with_empty_filters(): void
    {
        $stats = [
            'total_reviews' => 0,
            'approved_reviews' => 0,
            'pending_reviews' => 0,
            'average_rating' => 0,
        ];

        $this->reviewRepository
            ->shouldReceive('getStats')
            ->once()
            ->with([])
            ->andReturn($stats);

        $result = $this->reviewService->getReviewStats();

        $this->assertEquals(0, $result['total_reviews']);
    }

    // ========== getServiceReviews() Tests ==========

    public function test_get_service_reviews_returns_paginated_reviews(): void
    {
        $filters = ['rating' => 5, 'per_page' => 20];
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->reviewRepository
            ->shouldReceive('getForService')
            ->once()
            ->with(1, $filters)
            ->andReturn($paginator);

        $result = $this->reviewService->getServiceReviews(1, $filters);

        $this->assertSame($paginator, $result);
    }

    public function test_get_service_reviews_with_empty_filters(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->reviewRepository
            ->shouldReceive('getForService')
            ->once()
            ->with(1, [])
            ->andReturn($paginator);

        $result = $this->reviewService->getServiceReviews(1);

        $this->assertSame($paginator, $result);
    }

    // ========== getStaffReviews() Tests ==========

    public function test_get_staff_reviews_returns_paginated_reviews(): void
    {
        $filters = ['rating' => 4, 'per_page' => 10];
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->reviewRepository
            ->shouldReceive('getForStaff')
            ->once()
            ->with(5, $filters)
            ->andReturn($paginator);

        $result = $this->reviewService->getStaffReviews(5, $filters);

        $this->assertSame($paginator, $result);
    }

    public function test_get_staff_reviews_with_empty_filters(): void
    {
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->reviewRepository
            ->shouldReceive('getForStaff')
            ->once()
            ->with(5, [])
            ->andReturn($paginator);

        $result = $this->reviewService->getStaffReviews(5);

        $this->assertSame($paginator, $result);
    }
}
