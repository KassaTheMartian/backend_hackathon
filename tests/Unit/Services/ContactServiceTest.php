<?php

namespace Tests\Unit\Services;

use App\Models\ContactSubmission;
use App\Repositories\Contracts\ContactRepositoryInterface;
use App\Services\ContactService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class ContactServiceTest extends TestCase
{
    private ContactService $contactService;
    private ContactRepositoryInterface $contactRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contactRepository = Mockery::mock(ContactRepositoryInterface::class);
        $this->contactService = new ContactService($this->contactRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_submissions_with_filters()
    {
        // Arrange
        $filters = ['status' => 'new', 'search' => 'test'];
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->contactRepository->shouldReceive('getWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($paginator);

        // Act
        $result = $this->contactService->getSubmissions($filters);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_can_get_submissions_without_filters()
    {
        // Arrange
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->contactRepository->shouldReceive('getWithFilters')
            ->once()
            ->with([])
            ->andReturn($paginator);

        // Act
        $result = $this->contactService->getSubmissions();

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_can_get_submission_by_id()
    {
        // Arrange
        $submissionId = 1;
        $submission = Mockery::mock(ContactSubmission::class)->makePartial();
        $submission->id = $submissionId;
        $submission->name = 'John Doe';
        $submission->email = 'john@example.com';
        $submission->subject = 'Test Subject';
        $submission->message = 'Test message';
        $submission->status = 'new';

        $this->contactRepository->shouldReceive('getById')
            ->once()
            ->with($submissionId)
            ->andReturn($submission);

        // Act
        $result = $this->contactService->getSubmissionById($submissionId);

        // Assert
        $this->assertInstanceOf(ContactSubmission::class, $result);
        $this->assertEquals($submissionId, $result->id);
        $this->assertEquals('John Doe', $result->name);
    }

    /** @test */
    public function it_returns_null_when_submission_not_found()
    {
        // Arrange
        $submissionId = 999;

        $this->contactRepository->shouldReceive('getById')
            ->once()
            ->with($submissionId)
            ->andReturn(null);

        // Act
        $result = $this->contactService->getSubmissionById($submissionId);

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_can_create_submission()
    {
        // Arrange
        $data = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '0123456789',
            'subject' => 'Product Inquiry',
            'message' => 'I need more information',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0',
        ];

        $submission = Mockery::mock(ContactSubmission::class)->makePartial();
        $submission->id = 1;
        $submission->name = 'Jane Smith';
        $submission->email = 'jane@example.com';
        $submission->phone = '0123456789';

        $this->contactRepository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($submission);

        // Act
        $result = $this->contactService->createSubmission($data);

        // Assert
        $this->assertInstanceOf(ContactSubmission::class, $result);
        $this->assertEquals('Jane Smith', $result->name);
        $this->assertEquals('jane@example.com', $result->email);
    }

    /** @test */
    public function it_can_mark_submission_as_read()
    {
        // Arrange
        $submissionId = 1;
        $submission = Mockery::mock(ContactSubmission::class)->makePartial();
        $submission->id = $submissionId;
        $submission->name = 'John Doe';
        $submission->status = 'new';

        $readSubmission = Mockery::mock(ContactSubmission::class)->makePartial();
        $readSubmission->id = $submissionId;
        $readSubmission->name = 'John Doe';
        $readSubmission->status = 'in_progress';

        $this->contactRepository->shouldReceive('getById')
            ->once()
            ->with($submissionId)
            ->andReturn($submission);

        $this->contactRepository->shouldReceive('markAsRead')
            ->once()
            ->with($submission)
            ->andReturn($readSubmission);

        // Act
        $result = $this->contactService->markAsRead($submissionId);

        // Assert
        $this->assertInstanceOf(ContactSubmission::class, $result);
        $this->assertEquals('in_progress', $result->status);
    }

    /** @test */
    public function it_returns_null_when_marking_non_existent_submission_as_read()
    {
        // Arrange
        $submissionId = 999;

        $this->contactRepository->shouldReceive('getById')
            ->once()
            ->with($submissionId)
            ->andReturn(null);

        // Act
        $result = $this->contactService->markAsRead($submissionId);

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_can_mark_submission_as_unread()
    {
        // Arrange
        $submissionId = 1;
        $submission = Mockery::mock(ContactSubmission::class)->makePartial();
        $submission->id = $submissionId;
        $submission->name = 'John Doe';
        $submission->status = 'in_progress';

        $unreadSubmission = Mockery::mock(ContactSubmission::class)->makePartial();
        $unreadSubmission->id = $submissionId;
        $unreadSubmission->name = 'John Doe';
        $unreadSubmission->status = 'new';

        $this->contactRepository->shouldReceive('getById')
            ->once()
            ->with($submissionId)
            ->andReturn($submission);

        $this->contactRepository->shouldReceive('markAsUnread')
            ->once()
            ->with($submission)
            ->andReturn($unreadSubmission);

        // Act
        $result = $this->contactService->markAsUnread($submissionId);

        // Assert
        $this->assertInstanceOf(ContactSubmission::class, $result);
        $this->assertEquals('new', $result->status);
    }

    /** @test */
    public function it_returns_null_when_marking_non_existent_submission_as_unread()
    {
        // Arrange
        $submissionId = 999;

        $this->contactRepository->shouldReceive('getById')
            ->once()
            ->with($submissionId)
            ->andReturn(null);

        // Act
        $result = $this->contactService->markAsUnread($submissionId);

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_can_reply_to_submission()
    {
        // Arrange
        $submissionId = 1;
        $replyMessage = 'Thank you for your inquiry. We will get back to you soon.';

        $submission = Mockery::mock(ContactSubmission::class)->makePartial();
        $submission->id = $submissionId;
        $submission->name = 'John Doe';
        $submission->status = 'in_progress';

        $repliedSubmission = Mockery::mock(ContactSubmission::class)->makePartial();
        $repliedSubmission->id = $submissionId;
        $repliedSubmission->name = 'John Doe';
        $repliedSubmission->status = 'resolved';
        $repliedSubmission->response = $replyMessage;

        $this->contactRepository->shouldReceive('getById')
            ->once()
            ->with($submissionId)
            ->andReturn($submission);

        $this->contactRepository->shouldReceive('reply')
            ->once()
            ->with($submission, $replyMessage)
            ->andReturn($repliedSubmission);

        // Act
        $result = $this->contactService->replyToSubmission($submissionId, $replyMessage);

        // Assert
        $this->assertInstanceOf(ContactSubmission::class, $result);
        $this->assertEquals('resolved', $result->status);
        $this->assertEquals($replyMessage, $result->response);
    }

    /** @test */
    public function it_returns_null_when_replying_to_non_existent_submission()
    {
        // Arrange
        $submissionId = 999;
        $replyMessage = 'Test reply';

        $this->contactRepository->shouldReceive('getById')
            ->once()
            ->with($submissionId)
            ->andReturn(null);

        // Act
        $result = $this->contactService->replyToSubmission($submissionId, $replyMessage);

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_can_delete_submission()
    {
        // Arrange
        $submissionId = 1;

        $this->contactRepository->shouldReceive('delete')
            ->once()
            ->with($submissionId)
            ->andReturn(true);

        // Act
        $result = $this->contactService->deleteSubmission($submissionId);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_when_deleting_non_existent_submission()
    {
        // Arrange
        $submissionId = 999;

        $this->contactRepository->shouldReceive('delete')
            ->once()
            ->with($submissionId)
            ->andReturn(false);

        // Act
        $result = $this->contactService->deleteSubmission($submissionId);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_get_unread_count()
    {
        // Arrange
        $unreadCount = 5;

        $this->contactRepository->shouldReceive('getUnreadCount')
            ->once()
            ->andReturn($unreadCount);

        // Act
        $result = $this->contactService->getUnreadCount();

        // Assert
        $this->assertEquals(5, $result);
        $this->assertIsInt($result);
    }

    /** @test */
    public function it_returns_zero_when_no_unread_submissions()
    {
        // Arrange
        $this->contactRepository->shouldReceive('getUnreadCount')
            ->once()
            ->andReturn(0);

        // Act
        $result = $this->contactService->getUnreadCount();

        // Assert
        $this->assertEquals(0, $result);
    }

    /** @test */
    public function it_handles_multiple_submissions_with_different_statuses()
    {
        // Arrange
        $filters = ['status' => ['new', 'in_progress']];
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->contactRepository->shouldReceive('getWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($paginator);

        // Act
        $result = $this->contactService->getSubmissions($filters);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_creates_submission_with_minimal_required_fields()
    {
        // Arrange
        $data = [
            'name' => 'Min User',
            'email' => 'min@example.com',
            'message' => 'Short message',
        ];

        $submission = Mockery::mock(ContactSubmission::class)->makePartial();
        $submission->id = 1;
        $submission->name = 'Min User';
        $submission->email = 'min@example.com';

        $this->contactRepository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($submission);

        // Act
        $result = $this->contactService->createSubmission($data);

        // Assert
        $this->assertInstanceOf(ContactSubmission::class, $result);
        $this->assertEquals('Min User', $result->name);
    }

    /** @test */
    public function it_creates_submission_with_all_optional_fields()
    {
        // Arrange
        $data = [
            'name' => 'Full User',
            'email' => 'full@example.com',
            'phone' => '0987654321',
            'subject' => 'Detailed Inquiry',
            'message' => 'This is a detailed message with all fields',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Chrome/98.0',
        ];

        $submission = Mockery::mock(ContactSubmission::class)->makePartial();
        $submission->id = 1;
        $submission->name = 'Full User';
        $submission->email = 'full@example.com';
        $submission->phone = '0987654321';
        $submission->subject = 'Detailed Inquiry';

        $this->contactRepository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($submission);

        // Act
        $result = $this->contactService->createSubmission($data);

        // Assert
        $this->assertInstanceOf(ContactSubmission::class, $result);
        $this->assertEquals('Full User', $result->name);
        $this->assertEquals('0987654321', $result->phone);
        $this->assertEquals('Detailed Inquiry', $result->subject);
    }
}
