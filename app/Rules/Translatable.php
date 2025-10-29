<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a translatable field that can be either:
 * - a plain string (treated as default locale), or
 * - an associative array of {locale: value}
 */
class Translatable implements ValidationRule
{
    /**
     * Create a new translatable validation rule.
     *
     * @param bool $requiredDefault Whether the default locale is required
     * @param int|null $max Maximum length for the field
     */
    public function __construct(
        private readonly bool $requiredDefault = true,
        private readonly ?int $max = 255
    ) {}

    /**
     * Validate the translatable field.
     *
     * @param string $attribute The attribute name
     * @param mixed $value The value to validate
     * @param Closure $fail The closure to call if validation fails
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $supported = config('localization.supported', ['en', 'vi']);
        $default = config('localization.default', 'en');

        if (is_string($value)) {
            if ($this->max !== null && mb_strlen($value) > $this->max) {
                $fail("$attribute may not be greater than {$this->max} characters.");
            }
            return; // string is OK; will be normalized later
        }

        if (!is_array($value)) {
            $fail("$attribute must be a string or an object of translations.");
            return;
        }

        if ($this->requiredDefault && (!array_key_exists($default, $value) || !is_string($value[$default]) || trim($value[$default]) === '')) {
            $fail("$attribute.$default is required.");
            return;
        }

        foreach ($value as $locale => $text) {
            if (!in_array($locale, $supported, true)) {
                $fail("$attribute.$locale is not a supported locale.");
                return;
            }
            if (!is_string($text)) {
                $fail("$attribute.$locale must be a string.");
                return;
            }
            if ($this->max !== null && mb_strlen($text) > $this->max) {
                $fail("$attribute.$locale may not be greater than {$this->max} characters.");
                return;
            }
        }
    }
}


