<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FormattedNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove formatting dots and convert to float
        $numericValue = str_replace('.', '', $value);
        
        if (!is_numeric($numericValue)) {
            $fail('The :attribute must be a valid number.');
        }
        
        // Check if it's non-negative
        if ((float)$numericValue < 0) {
            $fail('The :attribute must be at least 0.');
        }
    }
}
