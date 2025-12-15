# Continents

## Setup

```php
use Ritechoice23\Worldable\Traits\HasContinent;

class User extends Model {
    use HasContinent;
}
```

## Continent Codes

| Code | Name |
|------|------|
| AF | Africa |
| AN | Antarctica |
| AS | Asia |
| EU | Europe |
| NA | North America |
| OC | Oceania |
| SA | South America |

## Basic Usage

```php
// Attach by name
$user->attachContinent('Africa');

// Attach by code
$user->attachContinent('AF');

// With groups
$user->attachContinent('Europe', 'residence');
$user->attachContinent('Africa', 'birthplace');
```

## Query Scopes

```php
// Users from Africa
User::whereInContinent('Africa')->get();

// By code
User::whereInContinent('AF')->get();

// With group
User::whereInContinent('Europe', 'residence')->get();

// By model instance
$africa = Continent::where('code', 'AF')->first();
User::whereInContinent($africa)->get();

// Exclude
User::whereNotInContinent('Africa')->get();
```

## Accessors

```php
echo $user->continent_name;  // "Africa"
echo $user->continent_code;  // "AF"
```

## Continent Model

```php
use Ritechoice23\Worldable\Models\Continent;

$continent = Continent::whereCode('AF')->first();
$countries = $continent->countries;  // All countries in continent
$subregions = $continent->subregions;  // All subregions
```

## Related

- [Countries](countries.md)
- [Subregions](subregions.md)
- [Groups](groups.md)
