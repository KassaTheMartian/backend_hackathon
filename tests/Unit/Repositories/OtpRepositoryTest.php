<?php

namespace Tests\Unit\Repositories;

use App\Models\OtpVerification;
use App\Repositories\Eloquent\OtpRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_create_find_mark_increment_delete(): void
    {
        $repo = new OtpRepository();
        $otp = $repo->create([
            'phone_or_email' => 'a@example.com',
            'purpose' => 'verify_email',
            'otp' => '123456',
            'expires_at' => now()->addMinutes(5),
            'attempts' => 0,
        ]);
        $this->assertNotNull($otp->id);

        $found = $repo->findLatestValid('a@example.com', 'verify_email');
        $this->assertNotNull($found);

        $this->assertTrue($repo->incrementAttempts($otp->id));
        $this->assertTrue($repo->markAsVerified($otp->id));

        $this->assertIsInt($repo->deleteByPhoneOrEmailAndPurpose('a@example.com', 'verify_email'));
    }
}


