<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
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
            return $this->ok($result);
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
            return $this->created($result, 'User registered successfully');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Registration failed', 'REGISTRATION_ERROR', 422);
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
        $this->authService->logout();
        return $this->noContent('Logged out');
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
     *   path="/api/v1/auth/forgot-password",
     *   summary="Send password reset link",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *   @OA\Response(response=404, description="Not Found", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * @param ForgotPasswordRequest $request The forgot password request containing email
     * @return JsonResponse The password reset link response
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->authService->sendPasswordResetLink($validated['email']);
            return $this->ok($result);
        } catch (\Exception $e) {
            return ApiResponse::notFound($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/reset-password",
     *   summary="Reset password with token",
     *   tags={"Auth"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"token","email","password","password_confirmation"},
     *       @OA\Property(property="token", type="string"),
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="password_confirmation", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope")),
     *   @OA\Response(response=400, description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ApiEnvelope"))
     * )
     * 
     * @param ResetPasswordRequest $request The reset password request containing token, email, and new password
     * @return JsonResponse The password reset response
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->authService->resetPassword(
                $validated['token'],
                $validated['email'],
                $validated['password']
            );
            return $this->ok($result);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 'Password Reset Failed', 'RESET_PASSWORD_ERROR', 400);
        }
    }
}


