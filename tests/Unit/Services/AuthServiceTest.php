<?php

namespace Tests\Unit\Services;

use App\Mail\OtpMail;
use App\Models\OtpVerification;
use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\OtpRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private AuthRepositoryInterface $authRepository;
    private OtpRepositoryInterface $otpRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock repositories
        $this->authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $this->otpRepository = Mockery::mock(OtpRepositoryInterface::class);

        // Create service instance with mocked dependencies
        $this->authService = new AuthService($this->authRepository, $this->otpRepository);

        // Fake mail
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = Hash::make($password);

        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => $email,
            'password' => $hashedPassword,
            'phone' => '0123456789',
            'avatar' => 'avatar.jpg',
            'language_preference' => 'vi',
            'is_admin' => false,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $user->id = 1;

        $token = 'fake-token-123';

        $this->authRepository->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($user);

        $this->authRepository->shouldReceive('createToken')
            ->once()
            ->with($user)
            ->andReturn($token);

        $this->authRepository->shouldReceive('update')
            ->once()
            ->with($user->id, Mockery::on(function ($arg) {
                return isset($arg['last_login_at']);
            }))
            ->andReturn(true);

        // Act
        $result = $this->authService->login($email, $password);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertEquals($token, $result['token']);
        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertEquals($user->id, $result['user']['id']);
        $this->assertEquals($user->email, $result['user']['email']);
    }

    /** @test */
    public function it_throws_exception_when_user_not_found_during_login()
    {
        // Arrange
        $email = 'nonexistent@example.com';
        $password = 'password123';

        $this->authRepository->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->authService->login($email, $password);
    }

    /** @test */
    public function it_throws_exception_when_password_is_invalid()
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'wrong-password';
        $hashedPassword = Hash::make('correct-password');

        $user = new User([
            'email' => $email,
            'password' => $hashedPassword,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->authRepository->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($user);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->authService->login($email, $password);
    }

    /** @test */
    public function it_throws_exception_when_user_is_inactive()
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = Hash::make($password);

        $user = new User([
            'email' => $email,
            'password' => $hashedPassword,
            'is_active' => false,
            'email_verified_at' => now(),
        ]);

        $this->authRepository->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($user);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->authService->login($email, $password);
    }

    /** @test */
    public function it_throws_exception_when_email_is_not_verified()
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = Hash::make($password);

        $user = new User([
            'email' => $email,
            'password' => $hashedPassword,
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        $this->authRepository->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($user);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->authService->login($email, $password);
    }

    /** @test */
    public function it_can_register_a_new_user()
    {
        // Arrange
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'phone' => '0123456789',
        ];

        $createdUser = new User([
            'id' => 1,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'],
            'language_preference' => 'vi',
            'email_verified_at' => null,
        ]);
        $createdUser->id = 1;

        $this->authRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userData) {
                return $arg['name'] === $userData['name']
                    && $arg['email'] === $userData['email']
                    && $arg['is_active'] === true
                    && $arg['language_preference'] === 'vi'
                    && Hash::check($userData['password'], $arg['password']);
            }))
            ->andReturn($createdUser);

        $otpRecord = new OtpVerification([
            'phone_or_email' => $userData['email'],
            'otp' => '123456',
            'type' => 'email',
            'purpose' => 'verify_email',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->otpRepository->shouldReceive('create')
            ->once()
            ->andReturn($otpRecord);

        // Act
        $result = $this->authService->register($userData);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals($createdUser->email, $result['user']['email']);
        $this->assertNull($result['user']['email_verified_at']);

        // Assert OTP email was sent
        Mail::assertSent(OtpMail::class);
    }

    /** @test */
    public function it_can_get_current_authenticated_user()
    {
        // Arrange
        $user = User::factory()->make(['id' => 1]);
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->authService->getCurrentUser();

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    /** @test */
    public function it_can_logout_current_user()
    {
        // Arrange
        $user = User::factory()->make(['id' => 1]);
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $this->authRepository->shouldReceive('revokeCurrentToken')
            ->once()
            ->with($user)
            ->andReturn(true);

        // Act
        $result = $this->authService->logout();

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_when_logout_without_authenticated_user()
    {
        // Arrange
        Auth::shouldReceive('user')
            ->once()
            ->andReturn(null);

        // Act
        $result = $this->authService->logout();

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_logout_from_all_devices()
    {
        // Arrange
        $user = User::factory()->make(['id' => 1]);
        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $this->authRepository->shouldReceive('revokeAllTokens')
            ->once()
            ->with($user)
            ->andReturn(true);

        // Act
        $result = $this->authService->logoutAll();

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_send_email_otp()
    {
        // Arrange
        $email = 'test@example.com';
        $purpose = 'verify_email';

        $otpRecord = new OtpVerification([
            'phone_or_email' => $email,
            'otp' => '123456',
            'type' => 'email',
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->otpRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($email, $purpose) {
                return $arg['phone_or_email'] === $email
                    && $arg['type'] === 'email'
                    && $arg['purpose'] === $purpose
                    && isset($arg['otp'])
                    && $arg['attempts'] === 0;
            }))
            ->andReturn($otpRecord);

        // Act
        $result = $this->authService->sendEmailOtp($email, $purpose);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        Mail::assertSent(OtpMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    /** @test */
    public function it_can_verify_email_otp_successfully()
    {
        // Arrange
        $email = 'test@example.com';
        $otp = '123456';
        $purpose = 'verify_email';

        $otpRecord = Mockery::mock(OtpVerification::class)->makePartial();
        $otpRecord->id = 1;
        $otpRecord->otp = $otp;
        $otpRecord->shouldReceive('isLockedOut')
            ->once()
            ->andReturn(false);
        $otpRecord->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $otpRecord->shouldReceive('getAttribute')
            ->with('otp')
            ->andReturn($otp);

        $user = new User([
            'id' => 1,
            'email' => $email,
            'email_verified_at' => null,
        ]);
        $user->id = 1;

        $this->otpRepository->shouldReceive('findLatestValid')
            ->once()
            ->with($email, $purpose)
            ->andReturn($otpRecord);

        $this->otpRepository->shouldReceive('markAsVerified')
            ->once()
            ->with($otpRecord->id)
            ->andReturn(true);

        $this->authRepository->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($user);

        $this->authRepository->shouldReceive('update')
            ->once()
            ->with($user->id, Mockery::on(function ($arg) {
                return isset($arg['email_verified_at']);
            }))
            ->andReturn(true);

        // Act
        $result = $this->authService->verifyEmailOtp($email, $otp, $purpose);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
    }

    /** @test */
    public function it_throws_exception_when_otp_not_found()
    {
        // Arrange
        $email = 'test@example.com';
        $otp = '123456';
        $purpose = 'verify_email';

        $this->otpRepository->shouldReceive('findLatestValid')
            ->once()
            ->with($email, $purpose)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->authService->verifyEmailOtp($email, $otp, $purpose);
    }

    /** @test */
    public function it_throws_exception_when_otp_is_locked_out()
    {
        // Arrange
        $email = 'test@example.com';
        $otp = '123456';
        $purpose = 'verify_email';

        $otpRecord = Mockery::mock(OtpVerification::class)->makePartial();
        $otpRecord->shouldReceive('isLockedOut')
            ->once()
            ->andReturn(true);

        $this->otpRepository->shouldReceive('findLatestValid')
            ->once()
            ->with($email, $purpose)
            ->andReturn($otpRecord);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->authService->verifyEmailOtp($email, $otp, $purpose);
    }

    /** @test */
    public function it_throws_exception_and_increments_attempts_when_otp_is_invalid()
    {
        // Arrange
        $email = 'test@example.com';
        $otp = '123456';
        $wrongOtp = '654321';
        $purpose = 'verify_email';

        $otpRecord = Mockery::mock(OtpVerification::class)->makePartial();
        $otpRecord->id = 1;
        $otpRecord->otp = $otp;
        $otpRecord->shouldReceive('isLockedOut')
            ->once()
            ->andReturn(false);
        $otpRecord->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $otpRecord->shouldReceive('getAttribute')
            ->with('otp')
            ->andReturn($otp);

        $this->otpRepository->shouldReceive('findLatestValid')
            ->once()
            ->with($email, $purpose)
            ->andReturn($otpRecord);

        $this->otpRepository->shouldReceive('incrementAttempts')
            ->once()
            ->with($otpRecord->id)
            ->andReturn(true);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->authService->verifyEmailOtp($email, $wrongOtp, $purpose);
    }

    /** @test */
    public function it_can_send_password_reset_otp()
    {
        // Arrange
        $email = 'test@example.com';

        $user = new User([
            'id' => 1,
            'email' => $email,
        ]);

        $otpRecord = new OtpVerification([
            'phone_or_email' => $email,
            'otp' => '123456',
            'type' => 'email',
            'purpose' => 'password_reset',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->authRepository->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($user);

        $this->otpRepository->shouldReceive('create')
            ->once()
            ->andReturn($otpRecord);

        // Act
        $result = $this->authService->sendPasswordResetOtp($email);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        Mail::assertSent(OtpMail::class);
    }

    /** @test */
    public function it_throws_exception_when_user_not_found_for_password_reset()
    {
        // Arrange
        $email = 'nonexistent@example.com';

        $this->authRepository->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn(null);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->authService->sendPasswordResetOtp($email);
    }

    /** @test */
    public function it_can_reset_password_with_valid_otp()
    {
        // Arrange
        $email = 'test@example.com';
        $otp = '123456';
        $newPassword = 'newpassword123';

        $otpRecord = Mockery::mock(OtpVerification::class)->makePartial();
        $otpRecord->id = 1;
        $otpRecord->otp = $otp;
        $otpRecord->shouldReceive('isLockedOut')
            ->once()
            ->andReturn(false);
        $otpRecord->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $otpRecord->shouldReceive('getAttribute')
            ->with('otp')
            ->andReturn($otp);

        $user = new User([
            'id' => 1,
            'email' => $email,
        ]);
        $user->id = 1;

        $this->otpRepository->shouldReceive('findLatestValid')
            ->once()
            ->with($email, 'password_reset')
            ->andReturn($otpRecord);

        $this->otpRepository->shouldReceive('markAsVerified')
            ->once()
            ->with($otpRecord->id)
            ->andReturn(true);

        $this->authRepository->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($user);

        $this->authRepository->shouldReceive('update')
            ->once()
            ->with($user->id, Mockery::on(function ($arg) use ($newPassword) {
                return isset($arg['password']) && Hash::check($newPassword, $arg['password']);
            }))
            ->andReturn(true);

        $this->authRepository->shouldReceive('revokeAllTokens')
            ->once()
            ->with($user)
            ->andReturn(true);

        // Act
        $result = $this->authService->resetPasswordWithOtp($email, $otp, $newPassword);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
    }

    /** @test */
    public function it_throws_exception_when_password_reset_otp_is_invalid()
    {
        // Arrange
        $email = 'test@example.com';
        $otp = '123456';
        $wrongOtp = '654321';
        $newPassword = 'newpassword123';

        $otpRecord = Mockery::mock(OtpVerification::class)->makePartial();
        $otpRecord->id = 1;
        $otpRecord->otp = $otp;
        $otpRecord->shouldReceive('isLockedOut')
            ->once()
            ->andReturn(false);
        $otpRecord->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $otpRecord->shouldReceive('getAttribute')
            ->with('otp')
            ->andReturn($otp);

        $this->otpRepository->shouldReceive('findLatestValid')
            ->once()
            ->with($email, 'password_reset')
            ->andReturn($otpRecord);

        $this->otpRepository->shouldReceive('incrementAttempts')
            ->once()
            ->with($otpRecord->id)
            ->andReturn(true);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->authService->resetPasswordWithOtp($email, $wrongOtp, $newPassword);
    }

    /** @test */
    public function it_can_send_test_email()
    {
        // Arrange
        $email = 'test@example.com';

        // Act
        $result = $this->authService->sendTestEmail($email);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('to', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertEquals($email, $result['to']);

        Mail::assertSent(OtpMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    /** @test */
    public function it_sets_default_language_preference_when_registering()
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $createdUser = new User([
            'id' => 1,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'language_preference' => 'vi',
        ]);
        $createdUser->id = 1;

        $this->authRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['language_preference'] === 'vi';
            }))
            ->andReturn($createdUser);

        $this->otpRepository->shouldReceive('create')
            ->once()
            ->andReturn(new OtpVerification());

        // Act
        $result = $this->authService->register($userData);

        // Assert
        $this->assertEquals('vi', $result['user']['language_preference']);
    }

    /** @test */
    public function it_respects_custom_language_preference_when_registering()
    {
        // Arrange
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'language_preference' => 'en',
        ];

        $createdUser = new User([
            'id' => 1,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'language_preference' => 'en',
        ]);
        $createdUser->id = 1;

        $this->authRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) {
                return $arg['language_preference'] === 'en';
            }))
            ->andReturn($createdUser);

        $this->otpRepository->shouldReceive('create')
            ->once()
            ->andReturn(new OtpVerification());

        // Act
        $result = $this->authService->register($userData);

        // Assert
        $this->assertEquals('en', $result['user']['language_preference']);
    }
}
