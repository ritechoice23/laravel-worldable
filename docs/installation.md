# Installation

## Requirements

- PHP 8.3+
- Laravel 11.0 or 12.0

## Quick Install

```bash
composer require ritechoice23/laravel-worldable
php artisan world:install --all
```

Done! You now have all world data (continents, countries, states, cities, currencies, languages, timezones).

## Selective Installation

```bash
# Minimal (countries + currencies)
php artisan world:install --countries --currencies

# Add more later
php artisan world:install --states --cities

# With polymorphic support
php artisan world:install --countries --worldables
```

## Components

| Component | Flag | Records | Notes |
|-----------|------|---------|-------|
| Continents | `--continents` | 7 | Required for countries |
| Subregions | `--subregions` | 24 | UN geoscheme regions |
| Countries | `--countries` | 250 | All countries |
| States | `--states` | 5,000+ | States/provinces |
| Cities | `--cities` | 148,000+ | Large dataset, uses streaming parser |
| Languages | `--languages` | 7,000+ | Fetched from remote source |
| Currencies | `--currencies` | 160+ | Fetched from remote source |
| Timezones | `--timezones` | 100+ | Major timezones |
| Worldables | `--worldables` | - | Polymorphic pivot table |

**Performance Features:**
- **Streaming JSON Parser**: Cities, countries, and states use memory-efficient streaming to handle large datasets
- **Progress Bars**: Real-time progress indicators during installation
- **Download Progress**: Shows file sizes and download status
- **Memory Optimized**: Handles 148k+ cities with minimal memory footprint

## Two Usage Modes

### 1. Direct Models (Traditional)

Use world data with standard Eloquent relationships:

```php
// Add foreign key to your migration
$table->foreignId('country_id')->nullable()->constrained('world_countries');

// Use standard relationship
class User extends Model {
    public function country() {
        return $this->belongsTo(Country::class);
    }
}

$user->country_id = Country::where('name', 'Nigeria')->first()->id;
```

### 2. Polymorphic Relationships (Zero-Migration)

Use the `Worldable` trait without modifying your schema:

```bash
# Install with polymorphic support
php artisan world:install --countries --worldables
```

```php
use Ritechoice23\Worldable\Traits\Worldable;

class User extends Model {
    use Worldable;
}

$user->attachCountry('Nigeria');
$user->attachCity('Lagos', 'billing');
```

**Note:** The `--worldables` flag installs the polymorphic pivot table. Skip it if you only need direct model access.

## Common Setups

### E-commerce

```bash
php artisan world:install --countries --currencies --worldables
```

### Full Location Data

```bash
php artisan world:install --all
```

**Warning:** Includes 100k+ cities. Only use if needed.

### International SaaS

```bash
php artisan world:install --countries --currencies --languages --timezones --worldables
```

## Dependencies

- Countries require Continents & Subregions
- States require Countries
- Cities require States

```bash
# Auto-install dependencies
php artisan world:install --states --with-dependencies

# Link orphaned records later
php artisan world:link
php artisan world:link --component=countries --dry-run
```

## Uninstall

```bash
# Remove specific components
php artisan world:uninstall --cities

# Remove everything
php artisan world:uninstall --all --force

# Strategies: nullify (default), block, cascade
php artisan world:uninstall --countries --strategy=cascade
```

## Health Check

```bash
php artisan world:health
php artisan world:health --detailed
php artisan world:health --json
```

## Configuration (Optional)

```bash
php artisan vendor:publish --tag=worldable-config
```

Edit `config/worldable.php` to customize table names.

## Next Steps

- [Basic Usage](basic-usage.md) - Get started
- [Countries](countries.md) - Working with countries
- [Groups](groups.md) - Context-aware relationships
