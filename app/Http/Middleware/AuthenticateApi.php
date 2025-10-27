<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateApi
{
    public function handle(Request $request, Closure $next)
    {
        // Demo-only: allow all requests or check for a simple token header
        return $next($request);
    }
}


