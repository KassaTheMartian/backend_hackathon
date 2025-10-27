<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasLocalization
{
    /**
     * Get the locale from request or user preference
     */
    protected function getLocale(Request $request): string
    {
        // 1. Check if user is authenticated and has language preference
        if ($request->user() && $request->user()->language_preference) {
            return $request->user()->language_preference;
        }

        // 2. Check query parameter
        if ($request->has('locale')) {
            $locale = $request->get('locale');
            if (in_array($locale, ['en', 'vi', 'ja', 'zh'])) {
                return $locale;
            }
        }

        // 3. Check Accept-Language header
        if ($request->hasHeader('Accept-Language')) {
            $locale = $request->getPreferredLanguage(['en', 'vi', 'ja', 'zh']);
            if ($locale) {
                return $locale;
            }
        }

        // 4. Default to Vietnamese
        return 'vi';
    }

    /**
     * Get localized value from JSON field
     */
    protected function getLocalizedValue(?array $jsonField, string $locale): string
    {
        if (!$jsonField) {
            return '';
        }

        // Try requested locale
        if (isset($jsonField[$locale])) {
            return $jsonField[$locale];
        }

        // Fallback to Vietnamese
        if (isset($jsonField['vi'])) {
            return $jsonField['vi'];
        }

        // Fallback to English
        if (isset($jsonField['en'])) {
            return $jsonField['en'];
        }

        // Return first available value
        return reset($jsonField) ?? '';
    }
}

