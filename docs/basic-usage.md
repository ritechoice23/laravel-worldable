# Basic Usage

## Add Trait to Model

```php
use Ritechoice23\Worldable\Traits\Worldable;

class User extends Model {
    use Worldable;  // All world features
}
```

Or use specific traits:

```php
use Ritechoice23\Worldable\Traits\{HasCountry, HasCurrency};

class Product extends Model {
    use HasCountry, HasCurrency;
}
```

## Core Operations

### Attach

```php
// By name
$user->attachCountry('Nigeria');
$user->attachCity('Lagos');

// By code
$user->attachCountry('NG');
$user->attachCurrency('USD');

// By ID
$user->attachCountry(1);

// With metadata
$user->attachCountry('NG', 'billing', [
    'tax_id' => '12345',
    'verified_at' => now(),
]);
```

### Detach

```php
$user->detachCountry('Nigeria');
$user->detachAllCities();
```

### Sync

```php
// Replace existing with new
$user->syncCountries(['Nigeria', 'Ghana']);
```

### Check

```php
if ($user->hasCountry('Nigeria')) {
    // User has Nigeria attached
}
```

### Retrieve

```php
$countries = $user->countries;
echo $user->country_name;  // "Nigeria"
echo $user->country_code;  // "NG"
```

## Query Scopes

```php
// Find users from Nigeria
User::whereFrom('Nigeria')->get();

// Find from continent
User::whereInContinent('Africa')->get();

// Find in city
User::whereInCity('Lagos')->get();

// Find in state
User::whereInState('California')->get();

// Products priced in USD
Product::wherePricedIn('USD')->get();

// Users who speak English
User::whereSpeaks('English')->get();
```

## Bulk Operations

```php
// Attach multiple
$user->attachCountries(['Nigeria', 'Ghana', 'Kenya']);
$user->attachCities(['Lagos', 'Accra'], 'visited');

// Sync (replace all)
$user->syncCountries(['Nigeria', 'Ghana']);
```

## Method Chaining

```php
$user->attachCountry('Nigeria')
     ->attachCity('Lagos')
     ->attachCurrency('NGN')
     ->attachLanguage('English');
```

## Next Steps

- [Countries](countries.md) - Country operations
- [Groups](groups.md) - Context groups (billing/shipping)
- [Meta Data](meta-data.md) - Custom metadata
- [Scopes](scopes.md) - Advanced querying
