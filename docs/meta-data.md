# Meta Data

Store custom JSON data on any world entity relationship.

## Supported Entities

All 8 entities support metadata: Countries, States, Cities, Continents, Subregions, Currencies, Languages, Timezones.

## Basic Usage

### Attach with Meta

```php
$user->attachCountry('Nigeria', 'billing', [
    'tax_id' => 'NG123456789',
    'vat_registered' => true,
]);

$order->attachCity('Lagos', 'shipping', [
    'delivery_notes' => 'Leave at gate',
    'preferred_time' => '10:00-12:00',
]);
```

### Get Meta

```php
// Get all meta
$meta = $user->getCountryMeta('Nigeria', group: 'billing');
// ['tax_id' => 'NG123456789', 'vat_registered' => true]

// Get specific key
$taxId = $user->getCountryMeta('Nigeria', 'tax_id', group: 'billing');
// 'NG123456789'

// With default value
$notes = $order->getCityMeta('Lagos', 'notes', 'No notes', 'shipping');
```

### Update Meta

```php
// Merge new data (default)
$user->updateCountryMeta('Nigeria', [
    'tax_exempt' => false,
], group: 'billing');

// Replace all meta
$user->updateCountryMeta('Nigeria', [
    'new_data' => 'value',
], group: 'billing', merge: false);
```

### Check Meta

```php
if ($user->hasCountryMeta('Nigeria', 'tax_id', 'billing')) {
    // Tax ID exists
}
```

## Methods by Entity

Each entity has the same three methods:

```php
// Countries
$user->getCountryMeta($country, $key, $default, $group)
$user->updateCountryMeta($country, $meta, $group, $merge)
$user->hasCountryMeta($country, $key, $group)

// States
$user->getStateMeta($state, $key, $default, $group)
$user->updateStateMeta($state, $meta, $group, $merge)
$user->hasStateMeta($state, $key, $group)

// Cities
$user->getCityMeta($city, $key, $default, $group)
$user->updateCityMeta($city, $meta, $group, $merge)
$user->hasCityMeta($city, $key, $group)

// Currencies
$product->getCurrencyMeta($currency, $key, $default, $group)
$product->updateCurrencyMeta($currency, $meta, $group, $merge)
$product->hasCurrencyMeta($currency, $key, $group)

// Languages
$user->getLanguageMeta($language, $key, $default, $group)
$user->updateLanguageMeta($language, $meta, $group, $merge)
$user->hasLanguageMeta($language, $key, $group)

// Timezones
$user->getTimezoneMeta($timezone, $key, $default, $group)
$user->updateTimezoneMeta($timezone, $meta, $group, $merge)
$user->hasTimezoneMeta($timezone, $key, $group)

// Continents
$user->getContinentMeta($continent, $key, $default, $group)
$user->updateContinentMeta($continent, $meta, $group, $merge)
$user->hasContinentMeta($continent, $key, $group)

// Subregions
$user->getSubregionMeta($subregion, $key, $default, $group)
$user->updateSubregionMeta($subregion, $meta, $group, $merge)
$user->hasSubregionMeta($subregion, $key, $group)
```

## Real-World Examples

### E-commerce Tax Data

```php
$order->attachCountry('US', 'billing', [
    'tax_id' => 'US-123-456-789',
    'tax_exempt' => false,
    'tax_rate' => 0.08,
]);

if ($order->hasCountryMeta('US', 'tax_exempt', 'billing')) {
    $taxRate = $order->getCountryMeta('US', 'tax_rate', 0, 'billing');
}
```

### Shipping Preferences

```php
$order->attachCity('New York', 'shipping', [
    'delivery_instructions' => 'Ring doorbell twice',
    'access_code' => '1234',
    'preferred_carrier' => 'UPS',
]);

$instructions = $order->getCityMeta(
    'New York',
    'delivery_instructions',
    'No special instructions',
    'shipping'
);
```

### Multi-Currency Pricing

```php
$product->attachCurrency('EUR', 'display', [
    'conversion_rate' => 1.18,
    'last_updated' => now(),
    'source' => 'ECB',
]);

$rate = $product->getCurrencyMeta('EUR', 'conversion_rate', 1.0, 'display');
```

### Language Proficiency

```php
$user->attachLanguage('Spanish', null, [
    'proficiency' => 'intermediate',
    'certified' => false,
    'years_studied' => 3,
]);

if ($user->hasLanguageMeta('Spanish', 'certified')) {
    $proficiency = $user->getLanguageMeta('Spanish', 'proficiency');
}
```

## Direct Pivot Access

```php
$country = $user->countries()->first();

// Get meta
$meta = $country->pivot->getMeta();
$taxId = $country->pivot->getMeta('tax_id', 'default');

// Set meta
$country->pivot->setMeta(['new' => 'data']);
$country->pivot->mergeMeta(['additional' => 'data']);
$country->pivot->removeMeta('key');
```

## Notes

- Stored as JSON
- Supports any JSON-serializable data
- Scoped to entity and group
- Merge by default, replace optionally
