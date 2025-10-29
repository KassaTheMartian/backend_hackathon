<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\ProfileService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    private $userRepository;
    private $promotionRepository;
    private $profileService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->promotionRepository = Mockery::mock(PromotionRepositoryInterface::class);

        $this->profileService = new ProfileService(
            $this->userRepository,
            $this->promotionRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ========== getProfile() Tests ==========

    public function test_get_profile_returns_user(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->name = 'John Doe';
        $user->email = 'john@example.com';

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $result = $this->profileService->getProfile(1);

        $this->assertSame($user, $result);
        $this->assertEquals('John Doe', $result->name);
    }

    public function test_get_profile_returns_null_when_user_not_found(): void
    {
        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->profileService->getProfile(999);

        $this->assertNull($result);
    }

    // ========== updateProfile() Tests ==========

    public function test_update_profile_updates_user_data(): void
    {
        $data = [
            'name' => 'Jane Doe',
            'phone' => '0123456789',
            'address' => '123 Main St',
        ];

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->name = 'Jane Doe';

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($user);

        $result = $this->profileService->updateProfile(1, $data);

        $this->assertSame($user, $result);
        $this->assertEquals('Jane Doe', $result->name);
    }

    public function test_update_profile_returns_null_when_user_not_found(): void
    {
        $data = ['name' => 'Test'];

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(999, $data)
            ->andReturn(null);

        $result = $this->profileService->updateProfile(999, $data);

        $this->assertNull($result);
    }

    public function test_update_profile_updates_language_preference(): void
    {
        $data = ['language_preference' => 'en'];

        $user = Mockery::mock(User::class)->makePartial();
        $user->language_preference = 'en';

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($user);

        $result = $this->profileService->updateProfile(1, $data);

        $this->assertEquals('en', $result->language_preference);
    }

    // ========== updateAvatar() Tests ==========

    public function test_update_avatar_uploads_new_avatar(): void
    {
        Storage::fake('public');

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->avatar = null;

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($userId, $data) use ($user) {
                $this->assertEquals(1, $userId);
                $this->assertArrayHasKey('avatar', $data);
                $this->assertStringStartsWith('avatars/', $data['avatar']);
                $user->avatar = $data['avatar'];
                return $user;
            });

        $result = $this->profileService->updateAvatar(1, $file);

        $this->assertNotNull($result);
        $this->assertNotNull($result->avatar);
        $this->assertStringStartsWith('avatars/', $result->avatar);

        Storage::disk('public')->assertExists($result->avatar);
    }

    public function test_update_avatar_deletes_old_avatar(): void
    {
        Storage::fake('public');

        $oldAvatarPath = 'avatars/old-avatar.jpg';
        Storage::disk('public')->put($oldAvatarPath, 'content');

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->avatar = $oldAvatarPath;

        $file = UploadedFile::fake()->image('new-avatar.jpg');

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($userId, $data) use ($user) {
                $user->avatar = $data['avatar'];
                return $user;
            });

        $result = $this->profileService->updateAvatar(1, $file);

        $this->assertNotNull($result);
        Storage::disk('public')->assertMissing($oldAvatarPath);
        Storage::disk('public')->assertExists($result->avatar);
    }

    public function test_update_avatar_returns_null_when_user_not_found(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->profileService->updateAvatar(999, $file);

        $this->assertNull($result);
    }

    // ========== deleteAvatar() Tests ==========

    public function test_delete_avatar_removes_avatar(): void
    {
        Storage::fake('public');

        $avatarPath = 'avatars/avatar.jpg';
        Storage::disk('public')->put($avatarPath, 'content');

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->avatar = $avatarPath;

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['avatar' => null])
            ->andReturnUsing(function ($userId, $data) use ($user) {
                $user->avatar = null;
                return $user;
            });

        $result = $this->profileService->deleteAvatar(1);

        $this->assertNotNull($result);
        $this->assertNull($result->avatar);
        Storage::disk('public')->assertMissing($avatarPath);
    }

    public function test_delete_avatar_when_no_avatar_exists(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->avatar = null;

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['avatar' => null])
            ->andReturn($user);

        $result = $this->profileService->deleteAvatar(1);

        $this->assertNotNull($result);
        $this->assertNull($result->avatar);
    }

    public function test_delete_avatar_returns_null_when_user_not_found(): void
    {
        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->profileService->deleteAvatar(999);

        $this->assertNull($result);
    }

    // ========== changePassword() Tests ==========

    public function test_change_password_successfully_changes_password(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->password = bcrypt('old-password');

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $updatedUser = Mockery::mock(User::class)->makePartial();
        
        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($userId, $data) use ($updatedUser) {
                $this->assertEquals(1, $userId);
                $this->assertArrayHasKey('password', $data);
                $this->assertNotEquals('new-password', $data['password']); // Should be hashed
                return $updatedUser;
            });

        $result = $this->profileService->changePassword(1, 'old-password', 'new-password');

        $this->assertTrue($result);
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->password = bcrypt('old-password');

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $result = $this->profileService->changePassword(1, 'wrong-password', 'new-password');

        $this->assertFalse($result);
    }

    public function test_change_password_returns_false_when_user_not_found(): void
    {
        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->profileService->changePassword(999, 'old-password', 'new-password');

        $this->assertFalse($result);
    }

    // ========== updateLanguagePreference() Tests ==========

    public function test_update_language_preference_changes_language(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->language_preference = 'en';

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['language_preference' => 'en'])
            ->andReturn($user);

        $result = $this->profileService->updateLanguagePreference(1, 'en');

        $this->assertNotNull($result);
        $this->assertEquals('en', $result->language_preference);
    }

    public function test_update_language_preference_supports_multiple_languages(): void
    {
        $languages = ['en', 'vi', 'ja', 'zh'];

        foreach ($languages as $language) {
            $user = Mockery::mock(User::class)->makePartial();
            $user->language_preference = $language;

            $this->userRepository
                ->shouldReceive('update')
                ->once()
                ->with(1, ['language_preference' => $language])
                ->andReturn($user);

            $result = $this->profileService->updateLanguagePreference(1, $language);

            $this->assertEquals($language, $result->language_preference);
        }
    }

    public function test_update_language_preference_returns_null_when_user_not_found(): void
    {
        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(999, ['language_preference' => 'en'])
            ->andReturn(null);

        $result = $this->profileService->updateLanguagePreference(999, 'en');

        $this->assertNull($result);
    }

    // ========== deactivateAccount() Tests ==========

    public function test_deactivate_account_sets_is_active_to_false(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->is_active = false;

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['is_active' => false])
            ->andReturn($user);

        $result = $this->profileService->deactivateAccount(1);

        $this->assertNotNull($result);
        $this->assertFalse($result->is_active);
    }

    public function test_deactivate_account_returns_null_when_user_not_found(): void
    {
        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(999, ['is_active' => false])
            ->andReturn(null);

        $result = $this->profileService->deactivateAccount(999);

        $this->assertNull($result);
    }

    // ========== reactivateAccount() Tests ==========

    public function test_reactivate_account_sets_is_active_to_true(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->is_active = true;

        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['is_active' => true])
            ->andReturn($user);

        $result = $this->profileService->reactivateAccount(1);

        $this->assertNotNull($result);
        $this->assertTrue($result->is_active);
    }

    public function test_reactivate_account_returns_null_when_user_not_found(): void
    {
        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with(999, ['is_active' => true])
            ->andReturn(null);

        $result = $this->profileService->reactivateAccount(999);

        $this->assertNull($result);
    }

    // ========== getUserStats() Tests ==========

    public function test_get_user_stats_returns_complete_statistics(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->created_at = now()->subMonths(6);
        $user->last_login_at = now()->subHours(2);

        // First call: bookings()->count()
        $bookingsRelation1 = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $bookingsRelation1->shouldReceive('count')->once()->andReturn(15);

        // Second call: bookings()->where()->count()
        $bookingsRelation2 = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $completedBookingsRelation = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $completedBookingsRelation->shouldReceive('count')->once()->andReturn(12);
        $bookingsRelation2->shouldReceive('where')
            ->once()
            ->with('status', 'completed')
            ->andReturn($completedBookingsRelation);

        // Reviews
        $reviewsRelation = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $reviewsRelation->shouldReceive('count')->once()->andReturn(8);

        $user->shouldReceive('bookings')
            ->twice()
            ->andReturn($bookingsRelation1, $bookingsRelation2);

        $user->shouldReceive('reviews')
            ->once()
            ->andReturn($reviewsRelation);

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $result = $this->profileService->getUserStats(1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_bookings', $result);
        $this->assertArrayHasKey('completed_bookings', $result);
        $this->assertArrayHasKey('total_reviews', $result);
        $this->assertArrayHasKey('member_since', $result);
        $this->assertArrayHasKey('last_login', $result);
        $this->assertEquals(15, $result['total_bookings']);
        $this->assertEquals(12, $result['completed_bookings']);
        $this->assertEquals(8, $result['total_reviews']);
    }

    public function test_get_user_stats_handles_null_last_login(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->created_at = now();
        $user->last_login_at = null;

        // First call: bookings()->count()
        $bookingsRelation1 = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $bookingsRelation1->shouldReceive('count')->once()->andReturn(0);

        // Second call: bookings()->where()->count()
        $bookingsRelation2 = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $completedBookingsRelation = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $completedBookingsRelation->shouldReceive('count')->once()->andReturn(0);
        $bookingsRelation2->shouldReceive('where')
            ->once()
            ->with('status', 'completed')
            ->andReturn($completedBookingsRelation);

        // Reviews
        $reviewsRelation = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $reviewsRelation->shouldReceive('count')->once()->andReturn(0);

        $user->shouldReceive('bookings')
            ->twice()
            ->andReturn($bookingsRelation1, $bookingsRelation2);

        $user->shouldReceive('reviews')
            ->once()
            ->andReturn($reviewsRelation);

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $result = $this->profileService->getUserStats(1);

        $this->assertIsArray($result);
        $this->assertNull($result['last_login']);
    }

    public function test_get_user_stats_returns_null_when_user_not_found(): void
    {
        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->profileService->getUserStats(999);

        $this->assertNull($result);
    }

    public function test_get_user_stats_returns_zero_for_new_user(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;
        $user->created_at = now();
        $user->last_login_at = now();

        // First call: bookings()->count()
        $bookingsRelation1 = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $bookingsRelation1->shouldReceive('count')->once()->andReturn(0);

        // Second call: bookings()->where()->count()
        $bookingsRelation2 = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $completedBookingsRelation = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $completedBookingsRelation->shouldReceive('count')->once()->andReturn(0);
        $bookingsRelation2->shouldReceive('where')
            ->once()
            ->with('status', 'completed')
            ->andReturn($completedBookingsRelation);

        // Reviews
        $reviewsRelation = Mockery::mock('Illuminate\Database\Eloquent\Relations\HasMany');
        $reviewsRelation->shouldReceive('count')->once()->andReturn(0);

        $user->shouldReceive('bookings')
            ->twice()
            ->andReturn($bookingsRelation1, $bookingsRelation2);

        $user->shouldReceive('reviews')
            ->once()
            ->andReturn($reviewsRelation);

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $result = $this->profileService->getUserStats(1);

        $this->assertEquals(0, $result['total_bookings']);
        $this->assertEquals(0, $result['completed_bookings']);
        $this->assertEquals(0, $result['total_reviews']);
    }

    // ========== getUserPromotions() Tests ==========

    public function test_get_user_promotions_returns_collection(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;

        $promotions = new Collection([
            (object)['id' => 1, 'code' => 'PROMO10'],
            (object)['id' => 2, 'code' => 'PROMO20'],
        ]);

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $this->promotionRepository
            ->shouldReceive('getUserPromotions')
            ->once()
            ->with($user)
            ->andReturn($promotions);

        $result = $this->profileService->getUserPromotions(1);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_get_user_promotions_returns_empty_when_user_not_found(): void
    {
        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(999)
            ->andReturn(null);

        $result = $this->profileService->getUserPromotions(999);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }

    public function test_get_user_promotions_returns_empty_when_no_promotions(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 1;

        $emptyCollection = new Collection([]);

        $this->userRepository
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($user);

        $this->promotionRepository
            ->shouldReceive('getUserPromotions')
            ->once()
            ->with($user)
            ->andReturn($emptyCollection);

        $result = $this->profileService->getUserPromotions(1);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }
}
