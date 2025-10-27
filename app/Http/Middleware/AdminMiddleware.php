<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
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