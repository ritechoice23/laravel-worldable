# API Reference

Complete API reference for Laravel Worldable package.

---

## Table of Contents

- [Traits](#traits)
  - [HasCountry](#hascountry)
  - [HasCity](#hascity)
  - [HasState](#hasstate)
  - [HasCurrency](#hascurrency)
  - [HasLanguage](#haslanguage)
  - [HasTimezone](#hastimezone)
  - [HasContinent](#hascontinent)
  - [HasSubregion](#hassubregion)
- [Validation Rules](#validation-rules)
- [Models](#models)

---

## Traits

### HasCountry

**Namespace:** `Ritechoice23\Worldable\Traits\HasCountry`

#### Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `attachCountry()` | `Country\|string\|int $country, ?string $group = null` | `self` | Attach a country to the model |
| `detachCountry()` | `Country\|string\|int $country, ?string $group = null` | `self` | Detach a country from the model |
| `syncCountries()` | `array $countries, ?string $group = null` | `self` | Sync countries (replace existing) |
| `detachAllCountries()` | `?string $group = null` | `self` | Detach all countries |
| `hasCountry()` | `Country\|string\|int $country, ?string $group = null` | `bool` | Check if model has country |
| `attachCountries()` | `array $countries, ?string $group = null` | `self` | Attach multiple countries |

#### Relationships

| Relationship | Return | Description |
|--------------|--------|-------------|
| `countries()` | `MorphToMany` | Get all attached countries |

#### Accessors

| Accessor | Return | Description |
|----------|--------|-------------|
| `country_name` | `?string` | Get the first country name |
| `country_code` | `?string` | Get the first country ISO code |
| `country_code_3` | `?string` | Get the first country ISO 3-letter code |

#### Scopes

| Scope | Parameters | Return | Description |
|-------|------------|--------|-------------|
| `whereFrom()` | `Country\|string\|int $country, ?string $group = null` | `Builder` | Filter by country |
| `whereNotFrom()` | `Country\|string\|int $country, ?string $group = null` | `Builder` | Exclude by country |

#### Usage Example

```php
use Ritechoice23\Worldable\Traits\HasCountry;

class User extends Model
{
    use HasCountry;
}

// Attach
$user->attachCountry('Nigeria');
$user->attachCountry('NG', 'citizenship');

// Query
User::whereFrom('Nigeria')->get();
User::whereNotFrom('US')->get();

// Access
$user->country_name; // "Nigeria"
$user->country_code; // "NG"
```

---

### HasCity

**Namespace:** `Ritechoice23\Worldable\Traits\HasCity`

#### Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `attachCity()` | `City\|string\|int $city, ?string $group = null` | `self` | Attach a city to the model |
| `detachCity()` | `City\|string\|int $city, ?string $group = null` | `self` | Detach a city from the model |
| `syncCities()` | `array $cities, ?string $group = null` | `self` | Sync cities (replace existing) |
| `detachAllCities()` | `?string $group = null` | `self` | Detach all cities |
| `hasCity()` | `City\|string\|int $city, ?string $group = null` | `bool` | Check if model has city |
| `attachCities()` | `array $cities, ?string $group = null` | `self` | Attach multiple cities |

#### Relationships

| Relationship | Return | Description |
|--------------|--------|-------------|
| `cities()` | `MorphToMany` | Get all attached cities |

#### Accessors

| Accessor | Return | Description |
|----------|--------|-------------|
| `city_name` | `?string` | Get the first city name |
| `city_coordinates` | `?array` | Get city coordinates `['lat' => float, 'lng' => float]` |

#### Scopes

| Scope | Parameters | Return | Description |
|-------|------------|--------|-------------|
| `whereInCity()` | `City\|string\|int $city, ?string $group = null` | `Builder` | Filter by city |
| `whereNotInCity()` | `City\|string\|int $city, ?string $group = null` | `Builder` | Exclude by city |

#### Usage Example

```php
use Ritechoice23\Worldable\Traits\HasCity;

class User extends Model
{
    use HasCity;
}

// Attach
$user->attachCity('Lagos');
$user->attachCity('New York', 'residence');

// Query
User::whereInCity('Lagos')->get();
User::whereNotInCity('Lagos')->get();

// Access
$user->city_name; // "Lagos"
$user->city_coordinates; // ['lat' => 6.5244, 'lng' => 3.3792]
```

---

### HasState

**Namespace:** `Ritechoice23\Worldable\Traits\HasState`

#### Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `attachState()` | `State\|string\|int $state, ?string $group = null` | `self` | Attach a state to the model |
| `detachState()` | `State\|string\|int $state, ?string $group = null` | `self` | Detach a state from the model |
| `syncStates()` | `array $states, ?string $group = null` | `self` | Sync states (replace existing) |
| `detachAllStates()` | `?string $group = null` | `self` | Detach all states |
| `hasState()` | `State\|string\|int $state, ?string $group = null` | `bool` | Check if model has state |
| `attachStates()` | `array $states, ?string $group = null` | `self` | Attach multiple states |

#### Relationships

| Relationship | Return | Description |
|--------------|--------|-------------|
| `states()` | `MorphToMany` | Get all attached states |

#### Accessors

| Accessor | Return | Description |
|----------|--------|-------------|
| `state_name` | `?string` | Get the first state name |
| `state_code` | `?string` | Get the first state code |

#### Scopes

| Scope | Parameters | Return | Description |
|-------|------------|--------|-------------|
| `whereInState()` | `State\|string\|int $state, ?string $group = null` | `Builder` | Filter by state |
| `whereNotInState()` | `State\|string\|int $state, ?string $group = null` | `Builder` | Exclude by state |

#### Usage Example

```php
use Ritechoice23\Worldable\Traits\HasState;

class User extends Model
{
    use HasState;
}

// Attach
$user->attachState('California');
$user->attachState('CA', 'residence');

// Query
User::whereInState('California')->get();
User::whereNotInState('Texas')->get();

// Access
$user->state_name; // "California"
$user->state_code; // "CA"
```

---

### HasCurrency

**Namespace:** `Ritechoice23\Worldable\Traits\HasCurrency`

#### Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `attachCurrency()` | `Currency\|string\|int $currency, ?string $group = null` | `self` | Attach a currency to the model |
| `detachCurrency()` | `Currency\|string\|int $currency, ?string $group = null` | `self` | Detach a currency from the model |
| `syncCurrencies()` | `array $currencies, ?string $group = null` | `self` | Sync currencies (replace existing) |
| `detachAllCurrencies()` | `?string $group = null` | `self` | Detach all currencies |
| `hasCurrency()` | `Currency\|string\|int $currency, ?string $group = null` | `bool` | Check if model has currency |
| `attachCurrencies()` | `array $currencies, ?string $group = null` | `self` | Attach multiple currencies |
| `formatMoney()` | `float $amount, ?string $group = null` | `string` | Format amount with currency symbol |
| `formatPrice()` | `float $amount, ?string $group = null` | `string` | Alias for formatMoney |

#### Relationships

| Relationship | Return | Description |
|--------------|--------|-------------|
| `currencies()` | `MorphToMany` | Get all attached currencies |

#### Accessors

| Accessor | Return | Description |
|----------|--------|-------------|
| `currency_symbol` | `?string` | Get the first currency symbol |
| `currency_code` | `?string` | Get the first currency code |
| `currency_name` | `?string` | Get the first currency name |

#### Scopes

| Scope | Parameters | Return | Description |
|-------|------------|--------|-------------|
| `wherePricedIn()` | `Currency\|string\|int $currency, ?string $group = null` | `Builder` | Filter by currency |
| `whereNotPricedIn()` | `Currency\|string\|int $currency, ?string $group = null` | `Builder` | Exclude by currency |

#### Usage Example

```php
use Ritechoice23\Worldable\Traits\HasCurrency;

class Product extends Model
{
    use HasCurrency;
}

// Attach
$product->attachCurrency('USD');
$product->attachCurrency('NGN', 'display');

// Format money
$product->formatMoney(1000); // "$1,000.00"

// Query
Product::wherePricedIn('USD')->get();

// Access
$product->currency_symbol; // "$"
$product->currency_code; // "USD"
```

---

### HasLanguage

**Namespace:** `Ritechoice23\Worldable\Traits\HasLanguage`

#### Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `attachLanguage()` | `Language\|string\|int $language, ?string $group = null` | `self` | Attach a language to the model |
| `detachLanguage()` | `Language\|string\|int $language, ?string $group = null` | `self` | Detach a language from the model |
| `syncLanguages()` | `array $languages, ?string $group = null` | `self` | Sync languages (replace existing) |
| `detachAllLanguages()` | `?string $group = null` | `self` | Detach all languages |
| `hasLanguage()` | `Language\|string\|int $language, ?string $group = null` | `bool` | Check if model has language |
| `attachLanguages()` | `array $languages, ?string $group = null` | `self` | Attach multiple languages |

#### Relationships

| Relationship | Return | Description |
|--------------|--------|-------------|
| `languages()` | `MorphToMany` | Get all attached languages |

#### Accessors

| Accessor | Return | Description |
|----------|--------|-------------|
| `language_name` | `?string` | Get the first language name |
| `language_code` | `?string` | Get the first language ISO code |
| `language_native_name` | `?string` | Get the first language native name |

#### Scopes

| Scope | Parameters | Return | Description |
|-------|------------|--------|-------------|
| `whereSpeaks()` | `Language\|string\|int $language, ?string $group = null` | `Builder` | Filter by language |
| `whereNotSpeaks()` | `Language\|string\|int $language, ?string $group = null` | `Builder` | Exclude by language |
| `whereLanguage()` | `Language\|string\|int $language, ?string $group = null` | `Builder` | Alias for whereSpeaks |

#### Usage Example

```php
use Ritechoice23\Worldable\Traits\HasLanguage;

class User extends Model
{
    use HasLanguage;
}

// Attach
$user->attachLanguage('English');
$user->attachLanguage('en', 'fluent');

// Query
User::whereSpeaks('English')->get();
User::whereNotSpeaks('French')->get();

// Access
$user->language_name; // "English"
$user->language_code; // "en"
$user->language_native_name; // "English"
```

---

### HasTimezone

**Namespace:** `Ritechoice23\Worldable\Traits\HasTimezone`

#### Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `attachTimezone()` | `Timezone\|string\|int $timezone, ?string $group = null` | `self` | Attach a timezone to the model |
| `detachTimezone()` | `Timezone\|string\|int $timezone, ?string $group = null` | `self` | Detach a timezone from the model |
| `syncTimezones()` | `array $timezones, ?string $group = null` | `self` | Sync timezones (replace existing) |
| `detachAllTimezones()` | `?string $group = null` | `self` | Detach all timezones |
| `hasTimezone()` | `Timezone\|string\|int $timezone, ?string $group = null` | `bool` | Check if model has timezone |
| `attachTimezones()` | `array $timezones, ?string $group = null` | `self` | Attach multiple timezones |
| `convertTime()` | `string\|Carbon $time, ?string $group = null` | `Carbon` | Convert time to model's timezone |

#### Relationships

| Relationship | Return | Description |
|--------------|--------|-------------|
| `timezones()` | `MorphToMany` | Get all attached timezones |

#### Accessors

| Accessor | Return | Description |
|----------|--------|-------------|
| `timezone_name` | `?string` | Get the first timezone name |
| `timezone_abbreviation` | `?string` | Get the first timezone abbreviation |
| `gmt_offset` | `?int` | Get the first timezone GMT offset in seconds |
| `gmt_offset_name` | `?string` | Get the first timezone GMT offset name (e.g., "UTC+01:00") |

#### Scopes

| Scope | Parameters | Return | Description |
|-------|------------|--------|-------------|
| `whereInTimezone()` | `Timezone\|string\|int $timezone, ?string $group = null` | `Builder` | Filter by timezone |

#### Usage Example

```php
use Ritechoice23\Worldable\Traits\HasTimezone;

class User extends Model
{
    use HasTimezone;
}

// Attach
$user->attachTimezone('America/New_York');
$user->attachTimezone('EST', 'display');

// Convert time
$localTime = $user->convertTime('2024-01-01 12:00:00');

// Access
$user->timezone_name; // "America/New_York"
$user->gmt_offset_name; // "UTC-05:00"
```

---

### HasContinent

**Namespace:** `Ritechoice23\Worldable\Traits\HasContinent`

#### Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `attachContinent()` | `Continent\|string\|int $continent, ?string $group = null` | `self` | Attach a continent to the model |
| `detachContinent()` | `Continent\|string\|int $continent, ?string $group = null` | `self` | Detach a continent from the model |
| `syncContinents()` | `array $continents, ?string $group = null` | `self` | Sync continents (replace existing) |
| `detachAllContinents()` | `?string $group = null` | `self` | Detach all continents |
| `hasContinent()` | `Continent\|string\|int $continent, ?string $group = null` | `bool` | Check if model has continent |
| `attachContinents()` | `array $continents, ?string $group = null` | `self` | Attach multiple continents |

#### Relationships

| Relationship | Return | Description |
|--------------|--------|-------------|
| `continents()` | `MorphToMany` | Get all attached continents |

#### Accessors

| Accessor | Return | Description |
|----------|--------|-------------|
| `continent_name` | `?string` | Get the first continent name |
| `continent_code` | `?string` | Get the first continent code |

#### Scopes

| Scope | Parameters | Return | Description |
|-------|------------|--------|-------------|
| `whereInContinent()` | `Continent\|string\|int $continent, ?string $group = null` | `Builder` | Filter by continent |
| `whereNotInContinent()` | `Continent\|string\|int $continent, ?string $group = null` | `Builder` | Exclude by continent |

#### Usage Example

```php
use Ritechoice23\Worldable\Traits\HasContinent;

class User extends Model
{
    use HasContinent;
}

// Attach
$user->attachContinent('Africa');
$user->attachContinent('AF', 'residence');

// Query
User::whereInContinent('Africa')->get();
User::whereNotInContinent('Europe')->get();

// Access
$user->continent_name; // "Africa"
$user->continent_code; // "AF"
```

---

### HasSubregion

**Namespace:** `Ritechoice23\Worldable\Traits\HasSubregion`

#### Methods

| Method | Parameters | Return | Description |
|--------|------------|--------|-------------|
| `attachSubregion()` | `Subregion\|string\|int $subregion, ?string $group = null` | `self` | Attach a subregion to the model |
| `detachSubregion()` | `Subregion\|string\|int $subregion, ?string $group = null` | `self` | Detach a subregion from the model |
| `syncSubregions()` | `array $subregions, ?string $group = null` | `self` | Sync subregions (replace existing) |
| `detachAllSubregions()` | `?string $group = null` | `self` | Detach all subregions |
| `hasSubregion()` | `Subregion\|string\|int $subregion, ?string $group = null` | `bool` | Check if model has subregion |
| `attachSubregions()` | `array $subregions, ?string $group = null` | `self` | Attach multiple subregions |

#### Relationships

| Relationship | Return | Description |
|--------------|--------|-------------|
| `subregions()` | `MorphToMany` | Get all attached subregions |

#### Accessors

| Accessor | Return | Description |
|----------|--------|-------------|
| `subregion_name` | `?string` | Get the first subregion name |
| `subregion_code` | `?string` | Get the first subregion code (UN M49) |

#### Scopes

| Scope | Parameters | Return | Description |
|-------|------------|--------|-------------|
| `whereInSubregion()` | `Subregion\|string\|int $subregion, ?string $group = null` | `Builder` | Filter by subregion |
| `whereNotInSubregion()` | `Subregion\|string\|int $subregion, ?string $group = null` | `Builder` | Exclude by subregion |

#### Usage Example

```php
use Ritechoice23\Worldable\Traits\HasSubregion;

class Company extends Model
{
    use HasSubregion;
}

// Attach
$company->attachSubregion('Western Africa');
$company->attachSubregion('011', 'market'); // UN M49 code

// Query
Company::whereInSubregion('Western Africa')->get();

// Access
$company->subregion_name; // "Western Africa"
$company->subregion_code; // "011"
```

---

## Validation Rules

### ValidCountry

**Namespace:** `Ritechoice23\Worldable\Rules\ValidCountry`

Validates country input (name, ISO 2-letter, ISO 3-letter, or ID).

```php
use Ritechoice23\Worldable\Rules\ValidCountry;

$request->validate([
    'country' => ['required', new ValidCountry()],
]);

// With custom message
$request->validate([
    'country' => [
        'required',
        (new ValidCountry())->withMessage('Please select a valid country')
    ],
]);
```

**Accepts:** `'Nigeria'`, `'NG'`, `'NGA'`, or `1`

---

### ValidCurrency

**Namespace:** `Ritechoice23\Worldable\Rules\ValidCurrency`

Validates currency input (code, name, or ID).

```php
use Ritechoice23\Worldable\Rules\ValidCurrency;

$request->validate([
    'currency' => ['required', new ValidCurrency()],
]);
```

**Accepts:** `'USD'`, `'US Dollar'`, or `1`

---

### ValidCity

**Namespace:** `Ritechoice23\Worldable\Rules\ValidCity`

Validates city input (name or ID).

```php
use Ritechoice23\Worldable\Rules\ValidCity;

$request->validate([
    'city' => ['nullable', new ValidCity()],
]);
```

**Accepts:** `'Lagos'` or `1`

---

### ValidLanguage

**Namespace:** `Ritechoice23\Worldable\Rules\ValidLanguage`

Validates language input (name, ISO code, or ID).

```php
use Ritechoice23\Worldable\Rules\ValidLanguage;

$request->validate([
    'language' => ['required', new ValidLanguage()],
]);
```

**Accepts:** `'English'`, `'en'`, or `1`

---

## Models

### Country

**Namespace:** `Ritechoice23\Worldable\Models\Country`

**Properties:**
- `id` - Primary key
- `name` - Country name
- `iso_code` - ISO 3166-1 alpha-2 code
- `iso_code_3` - ISO 3166-1 alpha-3 code
- `continent_id` - Foreign key to continents table

**Relationships:**
- `continent()` - BelongsTo Continent
- `states()` - HasMany State

---

### City

**Namespace:** `Ritechoice23\Worldable\Models\City`

**Properties:**
- `id` - Primary key
- `name` - City name
- `state_id` - Foreign key to states table
- `latitude` - Latitude coordinate
- `longitude` - Longitude coordinate

**Relationships:**
- `state()` - BelongsTo State

---

### State

**Namespace:** `Ritechoice23\Worldable\Models\State`

**Properties:**
- `id` - Primary key
- `name` - State name
- `code` - State code
- `country_id` - Foreign key to countries table

**Relationships:**
- `country()` - BelongsTo Country
- `cities()` - HasMany City

---

### Currency

**Namespace:** `Ritechoice23\Worldable\Models\Currency`

**Properties:**
- `id` - Primary key
- `name` - Currency name
- `code` - ISO 4217 currency code
- `symbol` - Currency symbol

**Methods:**
- `format(float $amount)` - Format amount with currency symbol

---

### Language

**Namespace:** `Ritechoice23\Worldable\Models\Language`

**Properties:**
- `id` - Primary key
- `name` - Language name
- `iso_code` - ISO 639-1 code
- `native_name` - Native language name

---

### Timezone

**Namespace:** `Ritechoice23\Worldable\Models\Timezone`

**Properties:**
- `id` - Primary key
- `zone_name` - Timezone identifier (e.g., "America/New_York")
- `abbreviation` - Timezone abbreviation (e.g., "EST")
- `gmt_offset` - GMT offset in seconds
- `gmt_offset_name` - GMT offset name (e.g., "UTC-05:00")

---

### Continent

**Namespace:** `Ritechoice23\Worldable\Models\Continent`

**Properties:**
- `id` - Primary key
- `name` - Continent name
- `code` - Continent code

**Relationships:**
- `countries()` - HasMany Country
- `subregions()` - HasMany Subregion

---

### Subregion

**Namespace:** `Ritechoice23\Worldable\Models\Subregion`

**Properties:**
- `id` - Primary key
- `name` - Subregion name
- `code` - UN M49 code
- `continent_id` - Foreign key to continents table

**Relationships:**
- `continent()` - BelongsTo Continent

**Scopes:**
- `ofContinent(Continent|string|int $continent)` - Filter by continent

---

## Common Patterns

### Groups

All traits support context groups for attaching multiple instances:

```php
// Attach with different contexts
$user->attachCountry('Nigeria', 'citizenship');
$user->attachCountry('United States', 'residence');

// Query by group
User::whereFrom('Nigeria', 'citizenship')->get();

// Get by group
$citizenship = $user->countries()->wherePivot('group', 'citizenship')->first();
```

### Fluent API

All methods return `$this` for method chaining:

```php
$user->attachCountry('Nigeria')
     ->attachCity('Lagos')
     ->attachCurrency('NGN')
     ->attachLanguage('English');
```

### Input Flexibility

Most methods accept multiple input types:

```php
// By name
$user->attachCountry('Nigeria');

// By code
$user->attachCountry('NG');

// By ID
$user->attachCountry(1);

// By model instance
$nigeria = Country::find(1);
$user->attachCountry($nigeria);
```

### Performance Optimization

All accessors check for loaded relationships:

```php
// Eager load for best performance
$users = User::with(['countries', 'cities', 'currencies'])->get();

foreach ($users as $user) {
    echo $user->country_name; // No N+1 query
    echo $user->city_name;    // No N+1 query
}
```
