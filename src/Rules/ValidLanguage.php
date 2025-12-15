<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Ritechoice23\Worldable\Models\Language;

/**
 * Validation rule for validating language input
 *
 * Accepts language names, ISO codes, or IDs
 */
class ValidLanguage implements ValidationRule
{
    protected ?string $message = null;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail($this->message ?? 'The :attribute must be a valid language name or code.');

            return;
        }

        if (is_numeric($value)) {
            if (Language::find($value) === null) {
                $fail($this->message ?? 'The :attribute must be a valid language name or code.');
            }

            return;
        }

        if (is_string($value)) {
            $exists = Language::where('name', $value)
                ->orWhere('iso_code', strtolower($value))
                ->exists();

            if (! $exists) {
                $fail($this->message ?? 'The :attribute must be a valid language name or code.');
            }

            return;
        }

        $fail($this->message ?? 'The :attribute must be a valid language name or code.');
    }

    public function withMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
