# Validation Rules

Validate geographic and linguistic data in Laravel requests.

## Available Rules

- `ValidCountry` - Validates country identifiers
- `ValidCurrency` - Validates currency codes
- `ValidCity` - Validates city names
- `ValidLanguage` - Validates language codes

## Valid Country

```php
use Ritechoice23\Worldable\Rules\ValidCountry;

$request->validate([
    'country' => ['required', new ValidCountry()],
]);
```

**Accepts:** `'Nigeria'`, `'NG'`, `'NGA'`, or `1`

### Custom Message

```php
$request->validate([
    'country' => [
        'required',
        (new ValidCountry())->withMessage('Please select a valid country'),
    ],
]);
```

## Valid Currency

```php
use Ritechoice23\Worldable\Rules\ValidCurrency;

$request->validate([
    'currency' => ['required', new ValidCurrency()],
]);
```

**Accepts:** `'USD'`, `'US Dollar'`, or `1`

## Valid City

```php
use Ritechoice23\Worldable\Rules\ValidCity;

$request->validate([
    'city' => ['nullable', new ValidCity()],
]);
```

**Accepts:** `'Lagos'`, `'New York'`, or `1`

## Valid Language

```php
use Ritechoice23\Worldable\Rules\ValidLanguage;

$request->validate([
    'language' => ['required', new ValidLanguage()],
]);
```

**Accepts:** `'English'`, `'en'`, or `1`

## Form Request Validation

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Ritechoice23\Worldable\Rules\{ValidCountry, ValidLanguage};

class UpdateProfileRequest extends FormRequest {
    public function rules(): array {
        return [
            'country' => ['required', new ValidCountry()],
            'languages' => ['array'],
            'languages.*' => [new ValidLanguage()],
        ];
    }

    public function messages(): array {
        return [
            'country.required' => 'Please select your country',
        ];
    }
}
```

## Validating Arrays

### Multiple Values

```php
$request->validate([
    'countries' => ['required', 'array'],
    'countries.*' => [new ValidCountry()],
]);
```

### Nested Arrays

```php
$request->validate([
    'locations' => ['required', 'array'],
    'locations.*.country' => ['required', new ValidCountry()],
    'locations.*.city' => ['required', new ValidCity()],
]);
```

## Combining Rules

```php
use Illuminate\Validation\Rule;

$request->validate([
    'country' => [
        'required',
        new ValidCountry(),
        'different:previous_country',
    ],
    
    'currency' => [
        'required',
        new ValidCurrency(),
        Rule::in(['USD', 'EUR', 'GBP']),
    ],
    
    'languages' => [
        'required',
        'array',
        'min:1',
        'max:5',
    ],
    'languages.*' => [new ValidLanguage()],
]);
```

## Conditional Validation

```php
use Illuminate\Validation\Rule;

$request->validate([
    'billing_country' => ['required', new ValidCountry()],
    
    'shipping_country' => [
        'required_if:needs_shipping,true',
        new ValidCountry(),
    ],
    
    'billing_city' => [
        Rule::requiredIf(fn () => $request->billing_country === 'US'),
        new ValidCity(),
    ],
]);
```

## Related

- [Basic Usage](basic-usage.md)
- [Countries](countries.md)
