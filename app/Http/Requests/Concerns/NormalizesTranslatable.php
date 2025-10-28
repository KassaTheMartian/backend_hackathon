<?php

namespace App\Http\Requests\Concerns;

trait NormalizesTranslatable
{
    /**
     * Normalize given keys so that:
     * - string => {default: string}
     * - array => filter unsupported locales, trim, drop empties
     */
    protected function normalizeTranslatable(array $keys): void
    {
        $supported = config('localization.supported', ['en', 'vi']);
        $default = config('localization.default', 'en');

        $input = $this->all();
        foreach ($keys as $key) {
            if (!array_key_exists($key, $input)) {
                continue;
            }
            $value = $input[$key];
            if (is_string($value)) {
                $normalized = [$default => trim($value)];
            } elseif (is_array($value)) {
                $normalized = [];
                foreach ($value as $locale => $text) {
                    if (!in_array($locale, $supported, true)) {
                        continue;
                    }
                    if (is_string($text)) {
                        $text = trim($text);
                        if ($text !== '') {
                            $normalized[$locale] = $text;
                        }
                    }
                }
                if (!isset($normalized[$default]) && isset($value[$default]) && is_string($value[$default])) {
                    $normalized[$default] = trim($value[$default]);
                }
            } else {
                continue;
            }
            $input[$key] = $normalized;
        }

        $this->replace($input);
    }
}


