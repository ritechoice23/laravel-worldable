<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Ritechoice23\Worldable\Models\City;

/**
 * Validation rule for validating city input
 *
 * Accepts city names or IDs
 */
class ValidCity implements ValidationRule
{
    protected ?string $message = null;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail($this->message ?? 'The :attribute must be a valid city name.');

            return;
        }

        if (is_numeric($value)) {
            if (City::find($value) === null) {
                $fail($this->message ?? 'The :attribute must be a valid city name.');
            }

            return;
        }

        if (is_string($value)) {
            if (! City::where('name', $value)->exists()) {
                $fail($this->message ?? 'The :attribute must be a valid city name.');
            }

            return;
        }

        $fail($this->message ?? 'The :attribute must be a valid city name.');
    }

    public function withMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
