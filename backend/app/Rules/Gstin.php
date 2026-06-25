<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a 15-character GSTIN (Indian GST Identification Number).
 *
 * Format: NNLLLLNNNNNAXXXXZ
 *  - 2 digit state code
 *  - 10 character PAN
 *  - 1 entity code (alphanumeric)
 *  - 1 character (Z by default)
 *  - 1 checksum (alphanumeric)
 *
 * Does not validate the checksum digit (requires a lookup table). The
 * format check is sufficient for input validation.
 */
class Gstin implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return; // allow nullable fields; use required rule separately
        }

        $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';

        if (! preg_match($pattern, mb_strtoupper($value))) {
            $fail('The :attribute must be a valid 15-character GSTIN.');
        }
    }
}