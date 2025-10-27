<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'OK', array $meta = null, int $status = 200): JsonResponse
    {
        return response()->json(self::envelope(true, $message, $data, null, $meta), $status);
    }

    public static function created(mixed $data = null, string $message = 'Created'): JsonResponse
    {
        return response()->json(self::envelope(true, $message, $data, null, null), 201);
    }

    public static function paginated(LengthAwarePaginator $paginator, string $message = 'OK'): JsonResponse
    {
        $meta = [
            'page' => $paginator->currentPage(),
            'page_size' => $paginator->perPage(),
            'total_count' => $paginator->total(),
            'total_pages' => $paginator->lastPage(),
            'has_next_page' => $paginator->currentPage() < $paginator->lastPage(),
            'has_previous_page' => $paginator->currentPage() > 1,
        ];
        
        return response()->json(self::envelope(true, $message, $paginator->items(), null, $meta));
    }

    public static function notFound(string $message = 'Not Found', string $code = 'NOT_FOUND'): JsonResponse
    {
        return response()->json(self::envelope(false, $message, null, [
            'type' => 'NotFound',
            'code' => $code,
        ], null), 404);
    }

    public static function validationError(array $errors, string $message = 'Validation failed', string $code = 'VALIDATION_FAILED'): JsonResponse
    {
        return response()->json(self::envelope(false, $message, null, [
            'type' => 'ValidationError',
            'code' => $code,
            'details' => $errors,
        ], null), 400);
    }

    public static function serverError(string $message = 'An unexpected error occurred', string $code = 'INTERNAL_ERROR', ?string $traceId = null): JsonResponse
    {
        return response()->json(self::envelope(false, $message, null, [
            'type' => 'InternalServerError',
            'code' => $code,
        ], null, $traceId), 500);
    }

    public static function error(string $message, string $title = 'Error', string $code = 'ERROR', int $status = 400, ?string $traceId = null): JsonResponse
    {
        return response()->json(self::envelope(false, $message, null, [
            'type' => $title,
            'code' => $code,
        ], null, $traceId), $status);
    }

    public static function forbidden(string $message = 'Forbidden', string $code = 'FORBIDDEN'): JsonResponse
    {
        return response()->json(self::envelope(false, $message, null, [
            'type' => 'Forbidden',
            'code' => $code,
        ], null), 403);
    }

    public static function unauthorized(string $message = 'Unauthorized', string $code = 'UNAUTHORIZED'): JsonResponse
    {
        return response()->json(self::envelope(false, $message, null, [
            'type' => 'Unauthorized',
            'code' => $code,
        ], null), 401);
    }

    public static function tooManyRequests(string $message = 'Too Many Requests', string $code = 'RATE_LIMIT_EXCEEDED'): JsonResponse
    {
        return response()->json(self::envelope(false, $message, null, [
            'type' => 'RateLimitExceeded',
            'code' => $code,
        ], null), 429);
    }

    private static function envelope(bool $success, string $message, mixed $data, ?array $error, ?array $meta, ?string $traceId = null): array
    {
        $request = request();
        $resolvedTraceId = $traceId ?? self::resolveTraceId($request);
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'error' => $error,
            'meta' => $meta,
            'trace_id' => $resolvedTraceId,
            'timestamp' => Carbon::now('UTC')->toISOString(),
        ];
    }

    private static function resolveTraceId(?Request $request): string
    {
        if (!$request) {
            return (string) Str::uuid();
        }
        return $request->headers->get('X-Request-Id')
            ?? $request->headers->get('X-Correlation-Id')
            ?? (string) Str::uuid();
    }
}


