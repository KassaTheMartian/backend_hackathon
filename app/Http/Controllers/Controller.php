<?php

namespace App\Http\Controllers;

use App\Exceptions\ResourceNotFoundException;
use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Base controller class for the application.
 *
 * Provides common methods for API responses and utilities.
 */
abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests, DispatchesJobs;

    /**
     * Return a successful JSON response.
     *
     * @param mixed $data The response data.
     * @param string $message The response message.
     * @return JsonResponse
     */
    protected function ok(mixed $data = null, string $message = 'OK'): JsonResponse
    {
        return ApiResponse::success($data, $message);
    }

    /**
     * Return a created JSON response.
     *
     * @param mixed $data The response data.
     * @param string $message The response message.
     * @return JsonResponse
     */
    protected function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return ApiResponse::created($data, $message);
    }

    /**
     * Return a no content JSON response.
     *
     * @param string $message The response message.
     * @return JsonResponse
     */
    protected function noContent(string $message = 'No Content'): JsonResponse
    {
        return ApiResponse::success(null, $message, null, 204);
    }

    /**
     * Return a paginated JSON response.
     *
     * @param mixed $paginator The paginator instance.
     * @param string $message The response message.
     * @return JsonResponse
     */
    protected function paginated(mixed $paginator, string $message = 'OK'): JsonResponse
    {
        return ApiResponse::paginated($paginator, $message);
    }

    /**
     * Throw a resource not found exception.
     *
     * @param string $name The resource name.
     * @return JsonResponse
     * @throws ResourceNotFoundException
     */
    protected function notFound(string $name = 'Resource'): JsonResponse
    {
        throw new ResourceNotFoundException($name);
    }

    /**
     * Return a forbidden JSON response.
     *
     * @param string $message The response message.
     * @return JsonResponse
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return ApiResponse::forbidden($message);
    }

    /**
     * Get the per page value from the request.
     *
     * @param Request $request The HTTP request.
     * @param int $default The default per page value.
     * @param int $max The maximum per page value.
     * @return int
     */
    protected function getPerPage(Request $request, int $default = 15, int $max = 100): int
    {
        $perPage = (int) $request->integer('per_page', $default);
        return max(1, min($perPage, $max));
    }

    /**
     * Get the page value from the request.
     *
     * @param Request $request The HTTP request.
     * @param int $default The default page value.
     * @return int
     */
    protected function getPage(Request $request, int $default = 1): int
    {
        return max(1, (int) $request->integer('page', $default));
    }

    /**
     * Get the authenticated user.
     *
     * @return Authenticatable|null
     */
    protected function user(): ?Authenticatable
    {
        return Auth::user();
    }
}
