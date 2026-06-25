<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a 10-character Indian PAN (Permanent Account Number).
 *
 * Format: AAAAA9999A (5 letters, 4 digits, 1 letter).
 */
class Pan implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if (! preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', mb_strtoupper($value))) {
            $fail('The :attribute must be a valid 10-character PAN.');
        }
    }
}