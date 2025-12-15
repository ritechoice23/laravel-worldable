# Timezones

## Setup

```php
use Ritechoice23\Worldable\Traits\HasTimezone;

class User extends Model {
    use HasTimezone;
}
```

## Attach Timezones

```php
// By zone name (IANA format)
$user->attachTimezone('America/New_York');
$user->attachTimezone('Asia/Tokyo');

// By abbreviation
$user->attachTimezone('EST');
$user->attachTimezone('JST');

// With context group
$user->attachTimezone('America/New_York', 'residence');
$user->attachTimezone('Asia/Tokyo', 'work');
```

## Time Conversion

```php
$user->attachTimezone('America/New_York');

// Convert current time
$localTime = $user->localTime();  // Carbon in user's timezone

// Convert specific datetime
$meetingTime = $user->localTime('2024-01-15 14:00:00');

// With groups
$user->attachTimezone('Asia/Tokyo', 'work');
$workTime = $user->localTime(now(), 'work');
```

## Accessors

```php
$user->attachTimezone('America/New_York');

echo $user->timezone_name;         // "America/New_York"
echo $user->timezone_abbreviation; // "EST" or "EDT"
echo $user->gmt_offset;           // -18000 (seconds from GMT)
```

## Timezone Model

```php
use Ritechoice23\Worldable\Models\Timezone;

// Find by zone name
$timezone = Timezone::whereZone('Asia/Tokyo')->first();

// Find by abbreviation
$timezone = Timezone::whereAbbreviation('JST')->first();

// Find by GMT offset
$timezones = Timezone::whereOffset(32400)->get();  // +9 hours in seconds

// Access properties
echo $timezone->name;            // "Japan Standard Time"
echo $timezone->zone_name;       // "Asia/Tokyo"
echo $timezone->abbreviation;    // "JST"
echo $timezone->gmt_offset;      // 32400
echo $timezone->gmt_offset_name; // "UTC+09:00"
```

## Query Scopes

```php
// Users in a specific timezone
User::whereInTimezone('America/New_York')->get();

// By abbreviation
User::whereInTimezone('EST')->get();

// With group
User::whereInTimezone('Asia/Tokyo', 'work')->get();

// By model instance
$timezone = Timezone::where('zone_name', 'America/New_York')->first();
User::whereInTimezone($timezone)->get();

// Exclude
User::whereNotInTimezone('America/New_York')->get();
```

## Bulk Operations

```php
// Sync timezones
$user->syncTimezones(['America/New_York', 'Europe/London']);

// Sync for specific group
$user->syncTimezones(['Asia/Tokyo'], 'work');

// Detach
$user->detachTimezone('America/New_York');
$user->detachTimezone('Asia/Tokyo', 'work');
$user->detachAllTimezones();
$user->detachAllTimezones('work');
```

## Real-World Example

```php
class Meeting extends Model {
    use HasTimezone;
}

$meeting = Meeting::create([...]);
$meeting->attachTimezone('America/New_York');

// Display for different users
foreach ($participants as $participant) {
    $participantTime = $participant->localTime($meeting->start_time);
    echo "Meeting starts at {$participantTime} {$participant->timezone_abbreviation}";
}
```

## Related

- [Groups](groups.md)
- [Countries](countries.md)
