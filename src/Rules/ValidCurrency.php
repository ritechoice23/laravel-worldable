<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Ritechoice23\Worldable\Models\Currency;

/**
 * Validation rule for validating currency input
 *
 * Accepts currency codes, names, or IDs
 */
class ValidCurrency implements ValidationRule
{
    protected ?string $message = null;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail($this->message ?? 'The :attribute must be a valid currency code or name.');

            return;
        }

        if (is_numeric($value)) {
            if (Currency::find($value) === null) {
                $fail($this->message ?? 'The :attribute must be a valid currency code or name.');
            }

            return;
        }

        if (is_string($value)) {
            $exists = Currency::where('code', strtoupper($value))
                ->orWhere('name', $value)
                ->exists();

            if (! $exists) {
                $fail($this->message ?? 'The :attribute must be a valid currency code or name.');
            }

            return;
        }

        $fail($this->message ?? 'The :attribute must be a valid currency code or name.');
    }

    public function withMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
