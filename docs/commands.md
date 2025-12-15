# Commands Reference

Complete reference for all Artisan commands provided by Laravel Worldable.

---

## Table of Contents

- [world:install](#worldinstall)
- [world:uninstall](#worlduninstall)
- [world:link](#worldlink)
- [world:health](#worldhealth)

---

## world:install

Install world data components (continents, countries, states, cities, currencies, languages, timezones).

### Syntax

```bash
php artisan world:install [options]
```

### Options

| Option | Description |
|--------|-------------|
| `--all` | Install all world data components |
| `--continents` | Install continents (7 records) |
| `--subregions` | Install subregions (24 records) |
| `--countries` | Install countries (250 records) |
| `--states` | Install states (5,000+ records) |
| `--cities` | Install cities (148,000+ records) |
| `--languages` | Install languages (7,000+ records from remote source) |
| `--currencies` | Install currencies (160+ records from remote source) |
| `--timezones` | Install timezones (100+ records) |
| `--worldables` | Install polymorphic pivot table |
| `--skip-large` | Skip large datasets (cities, states) when using `--all` |
| `--with-dependencies` | Automatically install required dependencies |
| `--auto-link` | Automatically link relationships after installation |
| `--no-link` | Skip relationship linking prompt |
| `--rollback-on-error` | Rollback migrations if seeding fails |

### Examples

```bash
# Install everything
php artisan world:install --all

# Install specific components
php artisan world:install --countries --currencies

# Install with dependencies
php artisan world:install --cities --with-dependencies

# Install without large datasets
php artisan world:install --all --skip-large

# Install and auto-link
php artisan world:install --countries --states --auto-link
```

### Interactive Mode

If no options are provided, the command enters interactive mode:

```bash
php artisan world:install
```

You'll be prompted to select components using space bar and enter to confirm.

### Progress Indicators

The command displays:
- **Download progress** with file sizes
- **Processing counts** for each component
- **Progress bars** during data insertion
- **Summary statistics** with orphaned record counts
- **Helpful tips** for next steps

### Example Output

```
Worldable Installation

Installation Plan:
  cities

Publishing migrations...
  2025_12_02_000004_create_world_cities_table.php

Running migrations...

Seeding world data...

Installing cities...
Downloading cities data from GitHub...
   This may take a while depending on your connection speed.

Downloaded 8.45 MB (compressed)

Decompressing cities data...
Decompressed 45.23 MB

Processing cities data with streaming parser...
   Using memory-efficient streaming to handle large datasets...

Counting cities...
   Found approximately 148,677 cities

[Progress: 100%]

Seeded 148,677 cities successfully.
Warning: 1,234 cities without country links (countries not installed).
Info: Run 'php artisan world:link' after installing dependencies.

cities installed successfully

World data installation complete!
```

### Dependencies

Some components require others:
- **Countries** require Continents & Subregions
- **States** require Countries
- **Cities** require States

Use `--with-dependencies` to auto-install required components.

### Performance

- **Memory Limit**: Automatically set to 512MB-1GB for large datasets
- **Time Limit**: Disabled (`set_time_limit(0)`) for long-running operations
- **Streaming Parser**: Cities, countries, and states use memory-efficient streaming
- **Batch Inserts**: Data inserted in chunks (500-1000 records per batch)

---

## world:uninstall

Remove world data components and optionally drop tables.

### Syntax

```bash
php artisan world:uninstall [options]
```

### Options

| Option | Description |
|--------|-------------|
| `--all` | Uninstall all world data components |
| `--continents` | Uninstall continents |
| `--subregions` | Uninstall subregions |
| `--countries` | Uninstall countries |
| `--states` | Uninstall states |
| `--cities` | Uninstall cities |
| `--languages` | Uninstall languages |
| `--currencies` | Uninstall currencies |
| `--timezones` | Uninstall timezones |
| `--worldables` | Uninstall polymorphic pivot table |
| `--force` | Skip confirmation prompt |
| `--strategy=<strategy>` | Dependency handling strategy: `nullify`, `block`, `cascade` |

### Strategies

| Strategy | Description |
|----------|-------------|
| `nullify` (default) | Set foreign keys to NULL for dependent records |
| `block` | Prevent uninstall if dependent records exist |
| `cascade` | Delete dependent records along with the component |

### Examples

```bash
# Uninstall specific component
php artisan world:uninstall --cities

# Uninstall with cascade
php artisan world:uninstall --countries --strategy=cascade

# Uninstall everything
php artisan world:uninstall --all --force

# Block if dependencies exist
php artisan world:uninstall --countries --strategy=block
```

### Example Output

```
Warning: Uninstalling: countries

Analyzing dependencies...
   Found 4,981 states depending on countries
   Found 148,677 cities depending on countries

Strategy: nullify
   States will have country_id set to NULL
   Cities will have country_id set to NULL

Are you sure? (yes/no) [no]: yes

Removing countries data...
[Progress: 100%]

Deleted 250 countries
Nullified 4,981 states
Nullified 148,677 cities

Uninstall complete!
```

---

## world:link

Link orphaned world data records to their dependencies.

### Syntax

```bash
php artisan world:link [options]
```

### Options

| Option | Description |
|--------|-------------|
| `--component=<component>` | Link specific component(s) (can be used multiple times) |
| `--dry-run` | Show what would be linked without making changes |
| `--force` | Skip confirmation prompt |

### Examples

```bash
# Link all orphaned records
php artisan world:link

# Link specific component
php artisan world:link --component=countries

# Link multiple components
php artisan world:link --component=countries --component=states

# Dry run to preview changes
php artisan world:link --component=cities --dry-run
```

### What It Does

The command:
1. Identifies orphaned records (records with NULL foreign keys)
2. Attempts to link them using available data (names, codes, etc.)
3. Reports success/failure statistics

### Example Output

```
Linking World Data

Analyzing orphaned records...
   Countries: 12 orphaned (missing continent/subregion links)
   States: 234 orphaned (missing country links)
   Cities: 1,234 orphaned (missing country/state links)

Linking countries...
[Progress: 100%]
Linked 10/12 countries
Warning: 2 countries could not be linked

Linking states...
[Progress: 100%]
Linked 230/234 states
Warning: 4 states could not be linked

Linking cities...
[Progress: 100%]
Linked 1,200/1,234 cities
Warning: 34 cities could not be linked

Linking complete!
   Total linked: 1,440
   Total failed: 40
```

---

## world:health

Check the health and integrity of world data.

### Syntax

```bash
php artisan world:health [options]
```

### Options

| Option | Description |
|--------|-------------|
| `--detailed` | Show detailed component information |
| `--json` | Output results in JSON format |
| `--component=<component>` | Check specific component only |

### Examples

```bash
# Basic health check
php artisan world:health

# Detailed check
php artisan world:health --detailed

# JSON output
php artisan world:health --json

# Check specific component
php artisan world:health --component=countries --detailed
```

### What It Checks

- **Table existence**: Verifies all tables exist
- **Record counts**: Shows number of records per component
- **Orphaned records**: Identifies records with missing dependencies
- **Data integrity**: Validates foreign key relationships
- **Missing data**: Detects components that should be installed

### Example Output (Basic)

```
World Data Health Check

Continents: 7 records
Subregions: 24 records
Countries: 250 records (12 orphaned)
States: 4,981 records (234 orphaned)
Cities: 148,677 records (1,234 orphaned)
Languages: 7,234 records
Currencies: 162 records
Timezones: 101 records
Worldables: Table exists

Overall Status: HEALTHY (with warnings)

Recommendations:
  - Run 'php artisan world:link' to fix 1,480 orphaned records
```

### Example Output (Detailed)

```
World Data Health Check (Detailed)

--- Continents ---
Table: world_continents
Records: 7
No orphaned records
All relationships valid

--- Countries ---
Table: world_countries
Records: 250
Orphaned: 12 (missing continent/subregion links)
  Breakdown:
    - Missing continent_id: 8
    - Missing subregion_id: 4
  Sample orphaned: Antarctica, Bouvet Island

--- Cities ---
Table: world_cities
Records: 148,677
Orphaned: 1,234 (missing country/state links)
  Breakdown:
    - Missing country_id: 456
    - Missing state_id: 778
  Top affected countries: Unknown (456), Various (778)

Overall Status: HEALTHY (with warnings)
```

### JSON Output

```bash
php artisan world:health --json
```

```json
{
  "status": "healthy_with_warnings",
  "components": {
    "continents": {
      "table": "world_continents",
      "exists": true,
      "count": 7,
      "orphaned": 0
    },
    "countries": {
      "table": "world_countries",
      "exists": true,
      "count": 250,
      "orphaned": 12
    }
  },
  "recommendations": [
    "Run 'php artisan world:link' to fix 1,480 orphaned records"
  ]
}
```

---

## Common Workflows

### Fresh Installation

```bash
# Install everything
php artisan world:install --all

# Or install selectively
php artisan world:install --countries --currencies --worldables
```

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

### Maintenance

```bash
# Regular health check
php artisan world:health

# Fix issues
php artisan world:link

# Re-install if needed
php artisan world:uninstall --cities --force
php artisan world:install --cities
```

---

## Performance Tips

1. **Use `--with-dependencies`** to avoid manual dependency management
2. **Use `--skip-large`** if you don't need cities/states
3. **Run during off-peak hours** for large datasets
4. **Monitor memory** with `php artisan world:health --detailed`
5. **Use `--dry-run`** before linking to preview changes

---

## Troubleshooting

### Installation Fails

```bash
# Check health
php artisan world:health --detailed

# Try with rollback
php artisan world:install --cities --rollback-on-error
```

### Orphaned Records

```bash
# Link them
php artisan world:link --component=cities

# Or re-install with dependencies
php artisan world:install --cities --with-dependencies
```

### Memory Issues

```bash
# The commands automatically set memory limits
# But you can increase PHP's memory_limit in php.ini if needed
memory_limit = 1024M
```

### Slow Installation

- **Expected**: Cities take 2-5 minutes due to 148k+ records
- **Use streaming parser**: Automatically enabled for large datasets
- **Check internet speed**: Data is downloaded from GitHub
