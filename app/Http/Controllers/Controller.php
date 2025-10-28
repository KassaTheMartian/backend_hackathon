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

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests, DispatchesJobs;

    protected function ok(mixed $data = null, string $message = 'OK'): JsonResponse
    {
        return ApiResponse::success($data, $message);
    }

    protected function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return ApiResponse::created($data, $message);
    }

    protected function noContent(string $message = 'No Content'): JsonResponse
    {
        return ApiResponse::success(null, $message, null, 204);
    }

    protected function paginated(mixed $paginator, string $message = 'OK'): JsonResponse
    {
        return ApiResponse::paginated($paginator, $message);
    }

    protected function notFound(string $name = 'Resource'): JsonResponse
    {
        throw new ResourceNotFoundException($name);
    }

    protected function getPerPage(Request $request, int $default = 15, int $max = 100): int
    {
        $perPage = (int) $request->integer('per_page', $default);
        return max(1, min($perPage, $max));
    }

    protected function getPage(Request $request, int $default = 1): int
    {
        return max(1, (int) $request->integer('page', $default));
    }

    protected function user(): ?Authenticatable
    {
        return auth()->user();
    }
}
