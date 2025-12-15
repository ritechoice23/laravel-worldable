# Countries

## Setup

```php
use Ritechoice23\Worldable\Traits\HasCountry;

class User extends Model {
    use HasCountry;
}
```

## Attach Countries

```php
// By name
$user->attachCountry('Nigeria');

// By ISO code (2 or 3 letter)
$user->attachCountry('NG');
$user->attachCountry('NGA');

// By ID
$user->attachCountry(1);

// By model instance
$country = Country::find(1);
$user->attachCountry($country);

// With context group
$user->attachCountry('Nigeria', 'citizenship');
$user->attachCountry('UK', 'residence');
```

## Relationships

```php
// Get all countries
$countries = $user->countries;

// Access data
foreach ($user->countries as $country) {
    echo $country->name;
    echo $country->iso_code;
    echo $country->calling_code;
}
```

## Accessors

```php
echo $user->country_name;  // "Nigeria"
echo $user->country_code;  // "NG"
```

## Query Scopes

```php
// Users from Nigeria
User::whereFrom('Nigeria')->get();
User::whereFrom('NG')->get();
User::whereFrom('NGA')->get();

// With context group
User::whereFrom('Nigeria', 'citizenship')->get();

// By model instance
$nigeria = Country::where('iso_code', 'NG')->first();
User::whereFrom($nigeria)->get();

// Exclude
User::whereNotFrom('Nigeria')->get();
User::whereNotFrom('Nigeria', 'citizenship')->get();
```

## Country Model

```php
use Ritechoice23\Worldable\Models\Country;

// Find by ISO code
$country = Country::whereCode('NG')->first();
$country = Country::whereCode('USA')->first();

// Search by name
$countries = Country::whereName('United')->get();

// Access data
echo $country->name;           // "Nigeria"
echo $country->iso_code;       // "NG"
echo $country->iso_code_3;     // "NGA"
echo $country->calling_code;   // "+234"
echo $country->currency_code;  // "NGN"
```

## Groups

```php
// Citizenship
$user->attachCountry('United States', 'citizenship');

// Residence
$user->attachCountry('United Kingdom', 'residence');

// Get by group
$citizenship = $user->countries()
    ->wherePivot('group', 'citizenship')
    ->get();
```

## Bulk Operations

```php
// Attach multiple
$user->attachCountries(['Nigeria', 'Ghana', 'Kenya']);

// Sync (replace all)
$user->syncCountries(['Nigeria', 'Ghana']);

// Sync specific group
$user->syncCountries(['United States'], 'citizenship');

// Detach
$user->detachCountry('Nigeria');
$user->detachCountry('Nigeria', 'citizenship');
$user->detachAllCountries();
$user->detachAllCountries('residence');
```

## Related

- [Continents](continents.md)
- [States](states.md)
- [Groups](groups.md)
