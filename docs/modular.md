# Modular Installation

## Overview

Laravel Worldable is designed to be **modular** and **lightweight**. You choose which world entities you need using command flags, and only those migrations run and data is seeded.

## Why Modular?

World data varies dramatically in size:

- **Continents:** 7 records (tiny)
- **Countries:** ~250 records (light)
- **Currencies:** ~150 records (light)
- **States:** ~5,000 records (medium)
- **Cities:** **100,000+ records** (very heavy!)
- **Worldables:** Pivot table (no data, migration only)

Most applications only need **countries and currencies**. Why force everyone to migrate 100,000 cities?

## Installation Approach

Use the `world:install` command with component flags:

```php
## Installation Approach

Use the `world:install` command with component flags:

```bash
# Install specific components
php artisan world:install --countries --currencies

# Install all components
php artisan world:install --all

# Install with worldables pivot table
php artisan world:install --countries --worldables
```

## How It Works

1. **Command flags** determine which components to install
2. **Only selected migrations** are published
3. **Only selected data** is seeded
4. **Worldables table** is optional - only needed for relationship traits

## Common Scenarios

### E-Commerce Platform

Needs: Countries for shipping, Currencies for pricing

```bash
php artisan world:install --continents --subregions --countries --currencies --worldables
```

**Result:** Fast migrations, small database, exactly what you need.

### International SaaS

Needs: Countries, Currencies, Languages, Timezones

```bash
php artisan world:install --continents --subregions --countries --currencies --languages --timezones --worldables
```

### Delivery/Logistics App

Needs: Everything including cities for precise delivery

```bash
php artisan world:install --all
```

**Warning:** Be prepared for a large cities table!

### Data-Only (No Relationships)

If you only need world data models without attaching them to your models:

```bash
# No worldables table needed
php artisan world:install --countries --currencies

# Use models directly
$countries = Country::all();
$usd = Currency::whereCode('USD')->first();
```

## Dependencies

Modules have dependencies:

- **Countries** require **Continents** and **Subregions**
- **States** require **Countries**
- **Cities** require **States**

The package handles these flexibly:

```bash
# Install without dependencies (creates NULL foreign keys)
php artisan world:install --states

# Install with automatic dependency resolution
php artisan world:install --states --with-dependencies

# Link orphaned records later
php artisan world:link
```

## Enabling/Disabling Components

### Initial Setup

Choose components during installation:

```bash
# Install specific components
php artisan world:install --countries --currencies

# Interactive selection
php artisan world:install
```

### Adding a Component Later

Simply run the install command again with the new component:

```bash
# Add states to existing installation
php artisan world:install --states

# With dependencies
php artisan world:install --states --with-dependencies

# Link relationships
php artisan world:link --component=states
```

### Removing a Component

Use the uninstall command:

```bash
# Remove cities
php artisan world:uninstall --cities

# Remove with confirmation skip
php artisan world:uninstall --cities --force

# Remove with cascade delete
php artisan world:uninstall --countries --strategy=cascade
```

## Performance Impact

### With All Components

- **9 migrations** (including worldables)
- **~100,000+ total records**
- **Slower initial setup**
- **Larger database**

### Countries + Currencies Only

- **5 migrations** (continents, subregions, countries, currencies, worldables)
- **~400 total records**
- **Fast setup**
- **Tiny database footprint**

### Without Worldables

If you don't need relationship traits:

- **One less migration**
- **No pivot table overhead**
- **Models still work** (`Country::all()`, etc.)

## Traits Behavior

### With Worldables Table

```php
use Worldable;

$user->attachCountry('Nigeria');  // Works
$user->countries;  // Returns relationship
```

### Without Worldables Table

```php
use Worldable;

$user->attachCountry('Nigeria');
// RuntimeException: The 'worldables' table does not exist.
// Please run 'php artisan world:install --worldables'

// But models still work:
$countries = Country::all();  // Works fine
```

## Custom Table Names

Customize table names in config:

```bash
# Publish config file
php artisan vendor:publish --tag=worldable-config
```

Edit `config/worldable.php`:

```php
'tables' => [
    'countries'  => 'locations',
    'currencies' => 'money_types',
    'worldables' => 'model_world_entities',  // Pivot table
    // ...
],
```

Models will automatically use custom table names.

## Best Practices

1. **Start minimal** - Install countries + currencies + worldables
2. **Add as needed** - Install more components when requirements grow
3. **Avoid cities** - Unless absolutely necessary
4. **Use --with-dependencies** - Ensures proper relationships
5. **Monitor health** - Run `php artisan world:health --detailed` regularly

## Example: Minimal Setup

```bash
# Install minimal components
php artisan world:install --continents --subregions --countries --currencies --worldables
```

```php
// In your model
class Product extends Model {
    use HasCountry, HasCurrency;
}

$product->attachCountry('United States');
$product->attachCurrency('USD');
```

**Result:** Lightweight, fast, focused.

## Health Monitoring

Check your installation status:

```bash
# Basic health check
php artisan world:health

# Detailed with orphaned records
php artisan world:health --detailed

# JSON output for CI/CD
php artisan world:health --json
```

## Related

- [Installation](installation.md) - Full setup guide
- [Basic Usage](basic-usage.md) - Using the traits
