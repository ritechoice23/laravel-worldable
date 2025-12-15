# Languages

## Setup

```php
use Ritechoice23\Worldable\Traits\HasLanguage;

class User extends Model {
    use HasLanguage;
}
```

## Attach Languages

```php
// By name
$user->attachLanguage('English');

// By ISO code
$user->attachLanguage('en');

// By ID
$user->attachLanguage(1);

// With context group
$user->attachLanguage('English', 'spoken');
$user->attachLanguage('Spanish', 'learning');
```

## Bulk Operations

```php
// Attach multiple
$user->attachLanguages(['English', 'Spanish', 'French'], 'spoken');

// Sync (replace all in group)
$user->syncLanguages(['German', 'Italian'], 'learning');

// Detach
$user->detachLanguage('French');
$user->detachAllLanguages('learning');
$user->detachAllLanguages();  // All groups
```

## Check Associations

```php
if ($user->hasLanguage('English')) {
    // User has English
}

if ($user->hasLanguage('Spanish', 'spoken')) {
    // User speaks Spanish
}

// By ISO code
if ($user->hasLanguage('en')) {
    // User has English
}
```

## Retrieve Languages

```php
// Get all languages
$languages = $user->languages;

// Get from specific group
$spokenLanguages = $user->languages()
    ->wherePivot('group', 'spoken')
    ->get();

// Access data
$language = $user->languages->first();
echo $language->name;         // "English"
echo $language->native_name;  // "English"
echo $language->iso_code;     // "en"
```

## Query Scopes

```php
// Users who speak English
User::whereSpeaks('English')->get();

// With group
User::whereSpeaks('Spanish', 'spoken')->get();

// By ISO code
User::whereSpeaks('en')->get();

// By model instance
$english = Language::where('iso_code', 'en')->first();
User::whereSpeaks($english)->get();

// Exclude
User::whereNotSpeaks('English')->get();
```

## Language Model

```php
use Ritechoice23\Worldable\Models\Language;

$language = Language::where('iso_code', 'en')->first();

echo $language->name;         // "English"
echo $language->native_name;  // "English"
echo $language->iso_code;     // "en"

// Non-English example
$spanish = Language::where('iso_code', 'es')->first();
echo $spanish->name;         // "Spanish"
echo $spanish->native_name;  // "Español"
```

## Real-World Examples

### Multilingual User Profile

```php
class User extends Model {
    use HasLanguage;

    public function getNativeLanguage(): ?Language {
        return $this->languages()->wherePivot('group', 'native')->first();
    }

    public function getSpokenLanguages(): Collection {
        return $this->languages()->wherePivot('group', 'spoken')->get();
    }

    public function speaks(Language|string $language): bool {
        return $this->hasLanguage($language, 'spoken');
    }
}

// Usage
$user->attachLanguage('English', 'native');
$user->attachLanguages(['English', 'Spanish'], 'spoken');
$user->attachLanguages(['French', 'German'], 'learning');

if ($user->speaks('Spanish')) {
    // Show Spanish content
}
```

### Content Localization

```php
class Article extends Model {
    use HasLanguage;

    public function addTranslation(Language|string $language): self {
        return $this->attachLanguage($language, 'translations');
    }

    public function hasTranslation(Language|string $language): bool {
        return $this->hasLanguage($language, 'translations');
    }
}

// Usage
$article->addTranslation('English');
$article->addTranslation('Spanish');
$article->addTranslation('French');

if ($article->hasTranslation('Spanish')) {
    // Show Spanish version
}

// Find articles in Spanish
$articles = Article::whereSpeaks('Spanish', 'translations')->get();
```

### Customer Support

```php
class SupportAgent extends Model {
    use HasLanguage;

    public function addSupportedLanguage(Language|string $language): self {
        return $this->attachLanguage($language, 'support');
    }

    public function canSupport(Language|string $language): bool {
        return $this->hasLanguage($language, 'support');
    }
}

class SupportTicket extends Model {
    use HasLanguage;

    public function setLanguage(Language|string $language): self {
        return $this->syncLanguages([$language], 'ticket');
    }

    public function assignToAvailableAgent(): ?SupportAgent {
        $language = $this->languages()->wherePivot('group', 'ticket')->first();

        return SupportAgent::whereSpeaks($language, 'support')
            ->where('is_available', true)
            ->first();
    }
}

// Usage
$agent->addSupportedLanguage('English');
$agent->addSupportedLanguage('Spanish');

$ticket->setLanguage('Spanish');
$assignedAgent = $ticket->assignToAvailableAgent();
```

## Notes

- Languages identified by ISO 639-1 codes (`iso_code`)
- Can query by `name`, `iso_code`, or `id`
- Includes both `name` (English) and `native_name` (localized)
- All associations stored in `worldables` table

## Related

- [Groups](groups.md)
- [Meta Data](meta-data.md)
