<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\SendPasswordResetOtpRequest;
use App\Http\Requests\Auth\ResetPasswordWithOtpRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @param AuthServiceInterface $authService The authentication service
     */
    public function __construct(private readonly AuthServiceInterface $authService)
    {
    }
    /**
     * @OA\Post(
     *   path="/api/v1/auth/login",
     *   summary="Login and create token",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="password", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * @param LoginRequest $request The login request containing email and password
     * @return JsonResponse The authentication response with token and user data
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();
            $result = $this->authService->login($credentials['email'], $credentials['password']);
            return $this->ok($result, 'Login successful');
        } catch (\Exception $e) {
            return ApiResponse::unauthorized($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/register",
     *   summary="Register new user",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name","email","password","password_confirmation"},
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="password_confirmation", type="string")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *   @OA\Response(response=422, description="Validation Error", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * @param RegisterRequest $request The registration request containing user data
     * @return JsonResponse The registration response with token and user data
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->authService->register($validated);
            return $this->created($result, 'Registration successful. Please verify your email.');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Registration failed', 'REGISTRATION_ERROR', 422);
        }
    }

    // Removed standalone sendOtp endpoint; email OTP is sent on registration and other flows as needed

    /**
     * @OA\Post(
     *   path="/api/v1/auth/verify-otp",
     *   summary="Verify OTP and mark email as verified",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","otp"},
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="otp", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            $result = $this->authService->verifyEmailOtp($validated['email'], $validated['otp'], 'verify_email');
            return $this->ok($result, 'Email verified');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Verification failed', 'OTP_ERROR', 422);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/v1/auth/me",
     *   summary="Current user",
     *   tags={"Auth"},
     *   security={{"sanctum": {}}},
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * @param Request $request The HTTP request
     * @return JsonResponse The current user information
     */
    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return ApiResponse::unauthorized();
        }
        
        return $this->ok([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'date_of_birth' => $user->date_of_birth ? ($user->date_of_birth instanceof \Carbon\Carbon ? $user->date_of_birth->format('Y-m-d') : $user->date_of_birth) : null,
            'gender' => $user->gender,
            'address' => $user->address,
            'language_preference' => $user->language_preference ?? 'vi',
            'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toISOString() : null,
            'created_at' => $user->created_at->toISOString(),
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/logout",
     *   summary="Revoke current token",
     *   tags={"Auth"},
     *   security={{"sanctum": {}}},
     *   @OA\Response(response=204, description="No Content")
     * )
     * 
     * @param Request $request The HTTP request
     * @return JsonResponse The logout response
     */
    public function logout(Request $request): JsonResponse
    {
        $success = $this->authService->logout();
        if (!$success) {
            return ApiResponse::unauthorized();
        }
        return $this->noContent('Logged out successfully');
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/logout-all",
     *   summary="Revoke all tokens for current user",
     *   tags={"Auth"},
     *   security={{"sanctum": {}}},
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * @param Request $request The HTTP request
     * @return JsonResponse The logout all response
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $success = $this->authService->logoutAll();
        if (!$success) {
            return ApiResponse::unauthorized('User not authenticated');
        }
        return $this->ok(['message' => 'Logged out from all devices']);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/send-reset-otp",
     *   summary="Send OTP for password reset",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function sendResetOtp(SendPasswordResetOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            $result = $this->authService->sendPasswordResetOtp($validated['email']);
            return $this->ok($result, 'OTP sent');
        } catch (\Exception $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/reset-password-otp",
     *   summary="Reset password using OTP",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","otp","password","password_confirmation"},
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="otp", type="string"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="password_confirmation", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     */
    public function resetPasswordWithOtp(ResetPasswordWithOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            $result = $this->authService->resetPasswordWithOtp($validated['email'], $validated['otp'], $validated['password']);
            return $this->ok($result, 'Password reset successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Password Reset Failed', 'RESET_PASSWORD_ERROR', 400);
        }
    }

    // Removed legacy token-based password reset endpoints in favor of OTP-only flow

    /**
     * @OA\Post(
     *   path="/api/v1/auth/test-email",
     *   summary="Test email sending",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email", example="test@example.com")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Email sent successfully", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *   @OA\Response(response=500, description="Email sending failed", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * @param Request $request The HTTP request containing email
     * @return JsonResponse The test email response
     */
    public function testEmail(Request $request): JsonResponse
    {
        try {
            $email = (string) $request->input('email');
            if (!$email) {
                return ApiResponse::error('Email is required', 'Validation Error', 'VALIDATION_ERROR', 422);
            }
            $result = $this->authService->sendTestEmail($email);
            return $this->ok($result, 'Email sent successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to send email: ' . $e->getMessage(), 'Email Sending Failed', 'EMAIL_ERROR', 500);
        }
    }
}


