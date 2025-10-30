<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check if the user is an admin.
 *
 * Ensures that only authenticated admin users can access protected routes.
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request The incoming request.
     * @param Closure $next The next middleware.
     * @return Response The response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'data' => null,
                'error' => [
                    'type' => 'AuthenticationError',
                    'code' => 'UNAUTHENTICATED',
                    'details' => [],
                ],
                'meta' => null,
                'trace_id' => $request->header('X-Trace-ID'),
                'timestamp' => now()->toISOString(),
            ], 401);
        }

        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions',
                'data' => null,
                'error' => [
                    'type' => 'AuthorizationError',
                    'code' => 'UNAUTHORIZED',
                    'details' => [],
                ],
                'meta' => null,
                'trace_id' => $request->header('X-Trace-ID'),
                'timestamp' => now()->toISOString(),
            ], 403);
        }

        return $next($request);
    }
}