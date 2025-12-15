# Query Scopes

Quick reference for all query scopes.

## Country Scopes

```php
// Find from country
User::whereFrom('Nigeria')->get();
User::whereFrom('NG')->get();  // By code
User::whereFrom($countryModel)->get();

// Exclude
User::whereNotFrom('Nigeria')->get();
User::whereNotFrom('Nigeria', 'citizenship')->get();
```

## Continent Scopes

```php
// Find in continent
User::whereInContinent('Africa')->get();
User::whereInContinent('AF')->get();  // By code
User::whereInContinent('Europe', 'residence')->get();

// Exclude
User::whereNotInContinent('Europe')->get();
```

## Subregion Scopes

```php
// Find in subregion
User::whereInSubregion('Western Africa')->get();
User::whereInSubregion('011')->get();  // By UN M49 code
User::whereInSubregion('Eastern Asia', 'market')->get();

// Exclude
User::whereNotInSubregion('Western Africa')->get();
```

## Location Scopes

```php
// By city
User::whereInCity('Lagos')->get();
User::whereInCity('New York', 'residence')->get();
User::whereNotInCity('Lagos')->get();

// By state
User::whereInState('California')->get();
User::whereInState('Lagos', 'residence')->get();
User::whereNotInState('California')->get();
```

## Currency Scopes

```php
// Priced in currency
Product::wherePricedIn('USD')->get();
Product::wherePricedIn('ngn')->get();  // Case insensitive
Product::wherePricedIn('USD', 'display')->get();

// Exclude
Product::whereNotPricedIn('USD')->get();
```

## Language Scopes

```php
// Speaks language
User::whereSpeaks('English')->get();
User::whereSpeaks('en')->get();  // By code
User::whereSpeaks('English', 'fluent')->get();

// Exclude
User::whereNotSpeaks('French')->get();
User::whereNotSpeaks('Spanish', 'native')->get();
```

## Timezone Scopes

```php
// In timezone
User::whereInTimezone('America/New_York')->get();
User::whereInTimezone('EST')->get();  // By abbreviation
User::whereInTimezone('Asia/Tokyo', 'work')->get();

// Exclude
User::whereNotInTimezone('America/New_York')->get();
```

## Combining Scopes

```php
// English-speaking users from Nigeria
$users = User::whereFrom('Nigeria')
    ->whereSpeaks('English')
    ->get();

// Products priced in USD from American companies
$products = Product::wherePricedIn('USD')
    ->whereHas('company', fn($q) => $q->whereFrom('United States'))
    ->get();

// With groups
$citizens = User::whereFrom('US', 'citizenship')->get();
$products = Product::wherePricedIn('EUR', 'display')->get();
```

## Eager Loading

```php
// Eager load relationships
$users = User::with(['countries', 'cities', 'currencies'])->get();

// With specific groups
$users = User::with([
    'countries' => fn($q) => $q->wherePivot('group', 'citizenship')
])->get();
```

## Custom Scopes

```php
class User extends Model {
    use Worldable;

    public function scopeFromCountries($query, array $countries) {
        return $query->whereHas('countries', function($q) use ($countries) {
            $q->whereIn('world_entity_id', function($sub) use ($countries) {
                $sub->select('id')
                    ->from('world_countries')
                    ->whereIn('iso_code', $countries);
            });
        });
    }
}

// Usage
$users = User::fromCountries(['NG', 'GH', 'KE'])->get();
```

## Related

- [Basic Usage](basic-usage.md)
- [Groups](groups.md)
