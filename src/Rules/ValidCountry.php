<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Ritechoice23\Worldable\Models\Country;

/**
 * Validation rule for validating country input
 *
 * Accepts country names, ISO codes (2 or 3 letter), or IDs
 */
class ValidCountry implements ValidationRule
{
    protected ?string $message = null;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail($this->message ?? 'The :attribute must be a valid country name or code.');

            return;
        }

        if (is_numeric($value)) {
            if (Country::find($value) === null) {
                $fail($this->message ?? 'The :attribute must be a valid country name or code.');
            }

            return;
        }

        if (is_string($value)) {
            $exists = Country::where('name', $value)
                ->orWhere('iso_code', strtoupper($value))
                ->orWhere('iso_code_3', strtoupper($value))
                ->exists();

            if (! $exists) {
                $fail($this->message ?? 'The :attribute must be a valid country name or code.');
            }

            return;
        }

        $fail($this->message ?? 'The :attribute must be a valid country name or code.');
    }

    public function withMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
