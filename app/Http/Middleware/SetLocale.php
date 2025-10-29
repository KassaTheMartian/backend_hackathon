<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $supported = config('localization.supported', ['en','vi','ja','zh']);
        $default = config('localization.default', 'en');

        // 1. Check if user is authenticated and has language preference
        if ($request->user() && $request->user()->language_preference) {
            app()->setLocale($request->user()->language_preference);
        }
        // 2. Check Accept-Language header first
        elseif ($request->hasHeader('Accept-Language')) {
            $preferred = $request->getPreferredLanguage($supported);
            app()->setLocale($preferred ?: $default);
        }
        // 3. Fallback to query parameters (for backward compatibility)
        else {
            $locale = $request->get('language') ?? $request->get('locale');
            if (is_string($locale) && in_array($locale, $supported, true)) {
                app()->setLocale($locale);
            } else {
                app()->setLocale($default);
            }
        }

        return $next($request);
    }
}


