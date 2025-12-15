# States

## Setup

```php
use Ritechoice23\Worldable\Traits\HasState;

class User extends Model {
    use HasState;
}
```

## Attach States

```php
// By name
$user->attachState('California');

// By code
$user->attachState('CA');

// By ID
$user->attachState(1);

// With context group
$user->attachState('Texas', 'residence');
$user->attachState('New York', 'workplace');
```

## Bulk Operations

```php
// Attach multiple
$user->attachStates(['California', 'Texas', 'Florida'], 'visited');

// Sync (replace all in group)
$user->syncStates(['Oregon', 'Washington'], 'residence');

// Detach
$user->detachState('California');
$user->detachAllStates('visited');
$user->detachAllStates();  // All groups
```

## Check Associations

```php
if ($user->hasState('California')) {
    // User has California
}

if ($user->hasState('Texas', 'residence')) {
    // User resides in Texas
}
```

## Retrieve States

```php
// Get all states
$states = $user->states;

// Get from specific group
$residenceStates = $user->states()
    ->wherePivot('group', 'residence')
    ->get();
```

## Query Scopes

```php
// Users in California
User::whereInState('California')->get();

// With group
User::whereInState('Texas', 'residence')->get();

// By code
User::whereInState('CA')->get();

// By model instance
$california = State::where('code', 'CA')->first();
User::whereInState($california)->get();

// Exclude
User::whereNotInState('California')->get();
```

## State Model

```php
use Ritechoice23\Worldable\Models\State;

$state = State::where('code', 'CA')->first();

echo $state->name;       // "California"
echo $state->code;       // "CA"
echo $state->country->name; // "United States"
```

## Real-World Examples

### Business Locations

```php
class Business extends Model {
    use HasState;

    public function addLocation(State|string $state): self {
        return $this->attachState($state, 'locations');
    }

    public function operatesInState(State|string $state): bool {
        return $this->hasState($state, 'locations');
    }
}

// Usage
$business->addLocation('California');
$business->addLocation('Texas');

if ($business->operatesInState('California')) {
    // Show California content
}

// Find businesses in Texas
$businesses = Business::whereInState('Texas', 'locations')->get();
```

### Shipping Restrictions

```php
class Product extends Model {
    use HasState;

    public function restrictShipping(array $states): self {
        return $this->syncStates($states, 'shipping_restricted');
    }

    public function canShipTo(State|string $state): bool {
        return !$this->hasState($state, 'shipping_restricted');
    }
}

// Usage
$product->restrictShipping(['Alaska', 'Hawaii']);

if ($product->canShipTo($user->getCurrentState())) {
    // Allow purchase
}

// Products that ship to California
$products = Product::whereNotInState('California', 'shipping_restricted')->get();
```

## Related

- [Countries](countries.md)
- [Cities](cities.md)
- [Groups](groups.md)
