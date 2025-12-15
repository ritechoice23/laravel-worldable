# Context Groups

Groups let you attach multiple instances of the same entity with different contexts.

## Use Cases

- Billing vs shipping addresses
- Citizenship vs residence
- Spoken vs written languages
- Display vs base currency

## Usage

### Attach with Groups

```php
// Citizenship vs residence
$user->attachCountry('United States', 'citizenship');
$user->attachCountry('United Kingdom', 'residence');

// Billing vs shipping
$user->attachCity('London', 'billing_address');
$user->attachCity('Lagos', 'shipping_address');
```

### Retrieve by Group

```php
// Get citizenship countries
$citizenships = $user->countries()
    ->wherePivot('group', 'citizenship')
    ->get();

// Get billing city
$billingCity = $user->cities()
    ->wherePivot('group', 'billing_address')
    ->first();
```

### Sync Specific Groups

```php
// Only update shipping cities, leave billing alone
$user->syncCities(['New York', 'Los Angeles'], 'shipping_address');
```

### Detach by Group

```php
// Remove all billing addresses
$user->detachAllCities('billing_address');

// Remove specific country from group
$user->detachCountry('Nigeria', 'residence');
```

## Real-World Examples

### E-Commerce Order

```php
class Order extends Model {
    use Worldable;
}

$order = Order::create([...]);

// Billing
$order->attachCountry('United States', 'billing');
$order->attachState('California', 'billing');
$order->attachCity('San Francisco', 'billing');

// Shipping
$order->attachCountry('Canada', 'shipping');
$order->attachState('Ontario', 'shipping');
$order->attachCity('Toronto', 'shipping');

// Pricing
$order->attachCurrency('USD', 'display');
$order->attachCurrency('CAD', 'settlement');
```

### International Team

```php
class Employee extends Model {
    use Worldable;
}

$employee = Employee::create([...]);

// Citizenship
$employee->attachCountry('Nigeria', 'citizenship');

// Work location
$employee->attachCountry('United Kingdom', 'work_location');
$employee->attachCity('London', 'work_location');

// Languages
$employee->attachLanguage('English', 'fluent');
$employee->attachLanguage('Yoruba', 'native');
$employee->attachLanguage('French', 'basic');
```

## Naming Conventions

- **Use snake_case**: `billing_address`, `work_location`
- **Be descriptive**: `primary_currency` not just `currency`
- **Be consistent**: Pick a pattern and stick to it

## Related

- [Scopes](scopes.md)
- [Meta Data](meta-data.md)
