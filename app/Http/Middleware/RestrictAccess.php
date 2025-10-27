<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictAccess
{
    public function handle(Request $request, Closure $next)
    {
        // Demo-only: deny if a simple query flag is present
        if ($request->query('deny') === '1') {
            return response()->json(['message' => 'Access denied (demo)'], 403);
        }
        return $next($request);
    }
}


