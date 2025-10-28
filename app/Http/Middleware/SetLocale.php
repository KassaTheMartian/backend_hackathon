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

        $locale = $request->get('language') ?? $request->get('locale');
        if (is_string($locale) && in_array($locale, $supported, true)) {
            app()->setLocale($locale);
        } else {
            $preferred = $request->getPreferredLanguage($supported);
            app()->setLocale($preferred ?: $default);
        }

        return $next($request);
    }
}


