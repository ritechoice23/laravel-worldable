# Laravel Worldable Documentation

Complete documentation for Laravel Worldable package.

## Getting Started

- **[Installation](installation.md)** - Install and configure the package
- **[Basic Usage](basic-usage.md)** - Learn the fundamentals
- **[Commands](commands.md)** - Complete Artisan commands reference

## Core Concepts

- **[Modular Installation](modular.md)** - Install only what you need
- **[Groups](groups.md)** - Context-aware relationships (billing/shipping, etc.)
- **[Meta Data](meta-data.md)** - Store custom data on relationships

## World Entities

- **[Continents](continents.md)** - 7 continents
- **[Subregions](subregions.md)** - 24 UN geoscheme regions
- **[Countries](countries.md)** - 250 countries with ISO codes
- **[States](states.md)** - 5,000+ states/provinces
- **[Cities](cities.md)** - 148,000+ cities with coordinates

## Localization

- **[Currencies](currencies.md)** - 160+ currencies with symbols and formatting
- **[Languages](languages.md)** - 7,000+ languages with ISO codes
- **[Timezones](timezones.md)** - 100+ timezones with GMT offsets

## API Reference

- **[API Reference](api-reference.md)** - Complete API documentation
- **[Validation Rules](validation-rules.md)** - Input validation rules
- **[Scopes](scopes.md)** - Query scopes reference

## Features

### Two Usage Modes

**1. Direct Models (Traditional)**
```php
// Add foreign key to your migration
$table->foreignId('country_id')->nullable()->constrained('world_countries');

// Use standard relationship
class User extends Model {
    public function country() {
        return $this->belongsTo(Country::class);
    }
}
```

**2. Polymorphic Relationships (Zero-Migration)**
```php
use Ritechoice23\Worldable\Traits\Worldable;

class User extends Model {
    use Worldable;
}

$user->attachCountry('Nigeria');
$user->attachCity('Lagos', 'billing');
```

### Key Features

| Feature | Description |
|---------|-------------|
| **8 World Entities** | Continents, Subregions, Countries, States, Cities, Currencies, Languages, Timezones |
| **Modular Install** | Install only what you need, add more later |
| **Two Usage Modes** | Traditional foreign keys OR polymorphic relationships |
| **Context Groups** | Separate billing/shipping, citizenship/residence |
| **Smart Resolution** | Accepts IDs, names, ISO codes automatically |
| **Query Scopes** | `whereFrom()`, `whereInCity()`, `wherePricedIn()`, `whereSpeaks()` |
| **Money Formatting** | `$product->formatMoney(100)` → "$100.00" with locale support |
| **Bulk Operations** | `$user->attachCountries(['NG', 'GH', 'KE'])` |
| **Custom Metadata** | Store extra data: `$user->attachCountry('NG', 'billing', ['tax_id' => '...'])` |
| **Health Checks** | `php artisan world:health --detailed` monitors data integrity |
| **Streaming Parser** | Memory-efficient handling of 148k+ cities |
| **Progress Indicators** | Real-time progress bars during installation |

### Performance Features

- **Streaming JSON Parser**: Cities, countries, and states use memory-efficient streaming
- **Progress Bars**: Real-time progress indicators during installation
- **Download Progress**: Shows file sizes and download status
- **Memory Optimized**: Handles 148k+ cities with minimal memory footprint
- **Batch Inserts**: Data inserted in chunks (500-1000 records per batch)
- **Automatic Memory Management**: Sets appropriate memory limits for large datasets

## Quick Examples

### E-commerce Checkout
```php
class Order extends Model {
    use Worldable;
}

$order
    ->attachCountry('United States', 'billing')
    ->attachState('California', 'billing')
    ->attachCountry('Canada', 'shipping')
    ->attachCity('Toronto', 'shipping')
    ->attachCurrency('USD', 'display')
    ->attachCurrency('CAD', 'settlement');
```

### User Profile
```php
class User extends Model {
    use Worldable;
}

$user
    ->attachCountry('Nigeria', 'citizenship')
    ->attachCountry('United States', 'residence')
    ->attachCity('Lagos', 'birth_place')
    ->attachLanguages(['English', 'Yoruba'], 'fluent')
    ->attachTimezone('Africa/Lagos');
```

### Product Pricing
```php
class Product extends Model {
    use Worldable;
}

$product->attachCurrency('USD');
$product->formatMoney(1000); // "$1,000.00"

// Multi-currency
$product->attachCurrencies(['USD', 'EUR', 'GBP']);
```

## Installation Workflow

```bash
# 1. Install package
composer require ritechoice23/laravel-worldable

# 2. Install world data
php artisan world:install --all

# 3. Check health
php artisan world:health

# 4. Link orphaned records (if any)
php artisan world:link

# 5. Verify
php artisan world:health --detailed
```

## Common Workflows

### Add Components Later
```bash
# Already have countries, add states
php artisan world:install --states --with-dependencies
```

### Fix Orphaned Records
```bash
# Check health
php artisan world:health --detailed

# Link orphaned records
php artisan world:link

# Verify
php artisan world:health
```

### Clean Uninstall
```bash
# Remove specific component
php artisan world:uninstall --cities

# Remove everything
php artisan world:uninstall --all --force
```

## Support

- **Issues**: [GitHub Issues](https://github.com/ritechoice23/laravel-worldable/issues)
- **Discussions**: [GitHub Discussions](https://github.com/ritechoice23/laravel-worldable/discussions)
- **Email**: daramolatunde23@gmail.com

## License

The MIT License (MIT). See [License File](../LICENSE.md).
