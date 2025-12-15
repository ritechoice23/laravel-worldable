# Cities

## Setup

```php
use Ritechoice23\Worldable\Traits\HasCity;

class User extends Model {
    use HasCity;
}
```

## Attach Cities

```php
// By name
$user->attachCity('Los Angeles');

// By ID
$user->attachCity(1);

// By model instance
$city = City::find(1);
$user->attachCity($city);

// With context group
$user->attachCity('New York', 'residence');
$user->attachCity('San Francisco', 'workplace');
```

## Bulk Operations

```php
// Attach multiple
$user->attachCities(['Los Angeles', 'San Diego', 'San Francisco'], 'visited');

// Sync (replace all in group)
$user->syncCities(['Seattle', 'Portland'], 'residence');

// Detach
$user->detachCity('Los Angeles');
$user->detachAllCities('visited');
$user->detachAllCities();  // All groups
```

## Check Associations

```php
if ($user->hasCity('Los Angeles')) {
    // User has Los Angeles
}

if ($user->hasCity('New York', 'residence')) {
    // User resides in New York
}
```

## Retrieve Cities

```php
// Get all cities
$cities = $user->cities;

// Get from specific group
$residenceCities = $user->cities()
    ->wherePivot('group', 'residence')
    ->get();

// Get first city
$primaryCity = $user->cities->first();
```

## Query Scopes

```php
// Users in Los Angeles
User::whereInCity('Los Angeles')->get();

// Users in New York (residence group)
User::whereInCity('New York', 'residence')->get();

// By ID
User::whereInCity(1)->get();

// By model instance
$losAngeles = City::where('name', 'Los Angeles')->first();
User::whereInCity($losAngeles)->get();

// Exclude
User::whereNotInCity('Los Angeles')->get();
User::whereNotInCity('New York', 'residence')->get();
```

## City Model

```php
use Ritechoice23\Worldable\Models\City;

$city = City::where('name', 'Los Angeles')->first();

// Access data
echo $city->name;              // "Los Angeles"
echo $city->latitude;          // 34.05223390
echo $city->longitude;         // -118.24368490
echo $city->state->name;       // "California"
echo $city->state->country->name; // "United States"
```

## Coordinates

```php
$city = City::where('name', 'Los Angeles')->first();
$latitude = $city->latitude;   // 34.05223390
$longitude = $city->longitude; // -118.24368490

// Find nearby cities
$nearbyCities = City::whereBetween('latitude', [$lat - 0.5, $lat + 0.5])
    ->whereBetween('longitude', [$lng - 0.5, $lng + 0.5])
    ->get();
```

## Real-World Examples

### Multi-City User Profile

```php
class User extends Model {
    use HasCity;

    public function getCurrentCity(): ?City {
        return $this->cities()->wherePivot('group', 'current')->first();
    }

    public function getBirthCity(): ?City {
        return $this->cities()->wherePivot('group', 'birth')->first();
    }

    public function getVisitedCities(): Collection {
        return $this->cities()->wherePivot('group', 'visited')->get();
    }
}

// Usage
$user->attachCity('Los Angeles', 'current');
$user->attachCity('Chicago', 'birth');
$user->attachCities(['New York', 'Miami', 'Seattle'], 'visited');

$currentCity = $user->getCurrentCity();
$visitedCount = $user->getVisitedCities()->count();
```

### Store Locations

```php
class Store extends Model {
    use HasCity;

    public function addLocation(City|string $city): self {
        return $this->attachCity($city, 'locations');
    }

    public function hasLocationIn(City|string $city): bool {
        return $this->hasCity($city, 'locations');
    }
}

// Usage
$store->addLocation('Los Angeles');
$store->addLocation('San Francisco');

if ($store->hasLocationIn('Los Angeles')) {
    // Show LA store details
}

// Find stores in San Francisco
$stores = Store::whereInCity('San Francisco', 'locations')->get();
```

### Event Venues

```php
class Event extends Model {
    use HasCity;

    public function setVenue(City|string $city): self {
        return $this->syncCities([$city], 'venue');  // Only one venue
    }

    public function getVenueCity(): ?City {
        return $this->cities()->wherePivot('group', 'venue')->first();
    }
}

// Usage
$event->setVenue('Las Vegas');

// Find events in Los Angeles
$events = Event::whereInCity('Los Angeles', 'venue')
    ->where('date', '>=', now())
    ->get();
```

## Notes

- Cities are identified by `name`
- Include latitude/longitude for geospatial features
- Cities belong to states → countries (hierarchical)
- All associations stored in `worldables` table
- Chaining supported: `$user->attachCity('LA')->attachCity('NYC')`

## Related

- [States](states.md)
- [Countries](countries.md)
- [Groups](groups.md)
