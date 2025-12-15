# Subregions

Geographical subdivisions of continents (UN M49 standard).

## Setup

```php
use Ritechoice23\Worldable\Traits\HasSubregion;

class User extends Model {
    use HasSubregion;
}
```

## Attach Subregions

```php
// By name
$user->attachSubregion('Western Africa');

// By UN M49 code
$user->attachSubregion('011');  // Western Africa

// By ID
$user->attachSubregion(1);

// With context group
$user->attachSubregion('Eastern Asia', 'market');
$user->attachSubregion('Western Europe', 'operations');
```

## Bulk Operations

```php
// Attach multiple
$user->attachSubregions(['Western Africa', 'Eastern Africa', 'Southern Africa'], 'operations');

// Sync (replace all in group)
$user->syncSubregions(['Eastern Asia', 'South-Eastern Asia'], 'market');

// Detach
$user->detachSubregion('Western Africa');
$user->detachAllSubregions('operations');
$user->detachAllSubregions();  // All groups
```

## Check Associations

```php
if ($user->hasSubregion('Western Africa')) {
    // User has Western Africa
}

if ($user->hasSubregion('Eastern Asia', 'market')) {
    // User operates in Eastern Asian market
}

// By code
if ($user->hasSubregion('011')) {
    // User has Western Africa (code 011)
}
```

## Retrieve Subregions

```php
// Get all subregions
$subregions = $user->subregions;

// Get from specific group
$marketSubregions = $user->subregions()
    ->wherePivot('group', 'market')
    ->get();

// Access data
$subregion = $user->subregions->first();
echo $subregion->name;              // "Western Africa"
echo $subregion->code;              // "011"
echo $subregion->continent->name;   // "Africa"
$countries = $subregion->countries; // Nigeria, Ghana, etc.
```

## Query Scopes

```php
// Users from Western Africa
User::whereInSubregion('Western Africa')->get();

// With group
User::whereInSubregion('Eastern Asia', 'market')->get();

// By UN M49 code
User::whereInSubregion('011')->get();

// By model instance
$westernAfrica = Subregion::where('code', '011')->first();
User::whereInSubregion($westernAfrica)->get();

// Exclude
User::whereNotInSubregion('Western Africa')->get();
```

## Subregion Model

```php
use Ritechoice23\Worldable\Models\Subregion;

// Find by code
$subregion = Subregion::whereCode('011')->first();

// Find by name
$subregions = Subregion::whereName('Western')->get();

// Find by continent
$africanSubregions = Subregion::ofContinent('Africa')->get();
$asianSubregions = Subregion::ofContinent('AS')->get();  // by code

// Hierarchical access
$subregion = Subregion::with(['continent', 'countries'])->first();
echo $subregion->continent->name;  // "Africa"
echo $subregion->name;             // "Western Africa"
echo $subregion->countries->count(); // 16 countries
```

## Real-World Examples

### Regional Business Operations

```php
class Company extends Model {
    use HasSubregion;

    public function addMarket(Subregion|string $subregion): self {
        return $this->attachSubregion($subregion, 'market');
    }

    public function operatesInSubregion(Subregion|string $subregion): bool {
        return $this->hasSubregion($subregion, 'market');
    }
}

// Usage
$company->addMarket('Western Africa');
$company->addMarket('Eastern Asia');

if ($company->operatesInSubregion('Western Africa')) {
    // Show Western African metrics
}

// Find companies in Eastern Asia
$companies = Company::whereInSubregion('Eastern Asia', 'market')->get();
```

### Content Distribution

```php
class StreamingService extends Model {
    use HasSubregion;

    public function setAvailableRegions(array $subregions): self {
        return $this->syncSubregions($subregions, 'available');
    }

    public function isAvailableIn(Subregion|string $subregion): bool {
        return $this->hasSubregion($subregion, 'available');
    }
}

// Usage
$service->setAvailableRegions(['Northern Europe', 'Western Europe', 'Southern Europe']);

if ($service->isAvailableIn($user->subregion_name)) {
    // Allow subscription
}
```

## UN M49 Codes

| Code | Subregion                 | Continent |
| ---- | ------------------------- | --------- |
| 011  | Western Africa            | Africa    |
| 014  | Eastern Africa            | Africa    |
| 015  | Northern Africa           | Africa    |
| 017  | Central Africa            | Africa    |
| 018  | Southern Africa           | Africa    |
| 030  | Eastern Asia              | Asia      |
| 034  | Southern Asia             | Asia      |
| 035  | South-Eastern Asia        | Asia      |
| 143  | Central Asia              | Asia      |
| 145  | Western Asia              | Asia      |
| 021  | Northern America          | Americas  |
| 013  | Central America           | Americas  |
| 029  | Caribbean                 | Americas  |
| 005  | South America             | Americas  |
| 151  | Eastern Europe            | Europe    |
| 154  | Northern Europe           | Europe    |
| 039  | Southern Europe           | Europe    |
| 155  | Western Europe            | Europe    |
| 053  | Australia and New Zealand | Oceania   |
| 054  | Melanesia                 | Oceania   |
| 057  | Micronesia                | Oceania   |
| 061  | Polynesia                 | Oceania   |

## Notes

- Uses UN M49 standard codes
- Identified by `code` column
- Hierarchical: Subregions → Continents → Countries
- All associations stored in `worldables` table

## Related

- [Continents](continents.md)
- [Countries](countries.md)
- [Groups](groups.md)
