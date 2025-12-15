# Contributing to Laravel Worldable

Thank you for considering contributing to Laravel Worldable! This document outlines the development workflow and guidelines.

## Development Setup

1. **Clone the repository**

```bash
git clone https://github.com/ritechoice23/laravel-worldable.git
cd laravel-worldable
```

2. **Install dependencies**

```bash
composer install
```

3. **Set up test environment**

```bash
# Copy test database config
cp phpunit.xml.dist phpunit.xml
```

## Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run specific test file
vendor/bin/pest tests/Unit/Models/CountryTest.php

# Run feature tests
vendor/bin/pest tests/Feature/
```

## Code Style

We use Laravel Pint for code formatting:

```bash
composer format
```

## Static Analysis

Run PHPStan for static analysis:

```bash
composer analyse
```

## Contributing Guidelines

### Pull Requests

1. **Fork the repository** and create a new branch
2. **Write tests** for new features
3. **Ensure all tests pass** (`composer test`)
4. **Format code** (`composer format`)
5. **Run static analysis** (`composer analyse`)
6. **Update documentation** if needed
7. **Submit pull request** with clear description

### Commit Messages

Use clear, descriptive commit messages:

```
Add worldables pivot table support
Fix state linker for orphaned records
Update installation documentation
```

### Adding New Features

When adding new features:

1. **Create appropriate files** in `src/` directory
2. **Add tests** in `tests/Unit/` or `tests/Feature/`
3. **Update documentation** in `docs/`
4. **Add examples** to README if it's a major feature
5. **Test installation commands** to ensure they work

### Package Structure

```
src/
├── Commands/           # Artisan commands
│   ├── Concerns/      # Shared command logic
│   ├── WorldInstallCommand.php
│   ├── WorldUninstallCommand.php
│   ├── WorldLinkCommand.php
│   └── WorldHealthCommand.php
├── Linkers/           # Relationship linkers
│   ├── AbstractLinker.php
│   ├── SubregionLinker.php
│   ├── CountryLinker.php
│   ├── StateLinker.php
│   └── CityLinker.php
├── Models/            # Eloquent models
│   ├── Continent.php
│   ├── Subregion.php
│   ├── Country.php
│   ├── State.php
│   ├── City.php
│   ├── Language.php
│   ├── Currency.php
│   ├── Timezone.php
│   └── Worldable.php
├── Traits/            # Model traits
│   ├── Concerns/      # Shared trait logic
│   ├── HasContinent.php
│   ├── HasSubregion.php
│   ├── HasCountry.php
│   ├── HasState.php
│   ├── HasCity.php
│   ├── HasLanguage.php
│   ├── HasCurrency.php
│   ├── HasTimezone.php
│   ├── Worldable.php
│   └── ManagesForeignKeys.php
├── Rules/             # Validation rules
└── Exceptions/        # Custom exceptions

database/
├── migrations/        # Package migrations
└── seeders/          # Data seeders

tests/
├── Unit/             # Unit tests
└── Feature/          # Feature tests

docs/                 # Documentation files
```

### Testing Guidelines

-   **Unit tests** for models, traits, and linkers
-   **Feature tests** for commands and workflows
-   **Test edge cases** and error handling
-   **Test modular installations** (components with/without dependencies)
-   **Test worldables pivot table** behavior

Example test:

```php
it('can attach country to model', function () {
    $user = User::factory()->create();
    $country = Country::factory()->create(['name' => 'Nigeria']);

    $user->attachCountry($country);

    expect($user->countries)->toHaveCount(1);
    expect($user->countries->first()->name)->toBe('Nigeria');
});

it('throws exception when worldables table missing', function () {
    Schema::dropIfExists('worldables');

    $user = User::factory()->create();

    expect(fn() => $user->countries())
        ->toThrow(RuntimeException::class, 'worldables');
});
```

### Documentation

When updating documentation:

-   Keep it **concise and scannable**
-   Include **code examples** with actual commands
-   Update **README.md** for major features
-   Add detailed docs in **docs/** directory
-   Use correct command flags (`--worldables`, `--with-dependencies`, etc.)
-   Update **installation.md** for installation changes
-   Update **modular.md** for component-related changes

## Architecture Decisions

### Command-Based Approach

We use Artisan commands for installation management (not config files):

```bash
php artisan world:install --countries --worldables
php artisan world:uninstall --cities --strategy=nullify
php artisan world:link --component=states
php artisan world:health --detailed
```

### Traits for Functionality

We use traits to organize functionality:

-   `HasCountry` - Country relationships
-   `HasCity` - City relationships
-   `HasCurrency` - Currency relationships
-   `Worldable` - All relationships combined
-   `ManagesForeignKeys` - Automatic FK nullification
-   `InteractsWithWorldEntities` - Shared relationship logic

### Linker Pattern

Single-responsibility linkers establish relationships retroactively:

-   `SubregionLinker` - Links subregions to continents
-   `CountryLinker` - Links countries to continents and subregions
-   `StateLinker` - Links states to countries
-   `CityLinker` - Links cities to states and countries

### Model Events for FK Management

Foreign keys are managed via model `saving` events:

```php
trait ManagesForeignKeys
{
    protected static function bootManagesForeignKeys(): void
    {
        static::saving(function ($model) {
            foreach ($model->getForeignKeyDefinitions() as $column => $config) {
                if (!Schema::hasTable($config['table'])) {
                    $model->{$column} = null;
                }
            }
        });
    }
}
```

### Optional Worldables Table

The `worldables` pivot table is optional:

-   **With worldables**: Full relationship traits work (`attachCountry()`, etc.)
-   **Without worldables**: Only models work (`Country::all()`, etc.)
-   **Clear exceptions**: Helpful error messages guide users

### Smart Defaults

Commands support intelligent defaults:

```bash
world:install              # Interactive selection
world:install --all        # Install everything
world:install --countries  # Warns about dependencies
world:install --countries --with-dependencies  # Auto-includes deps
```

## Testing Installation Commands

When testing commands:

```bash
# Test modular installation
php artisan world:install --countries

# Test with dependencies
php artisan world:install --states --with-dependencies

# Test worldables separately
php artisan world:install --worldables

# Test linking
php artisan world:link --dry-run

# Test health check
php artisan world:health --detailed

# Test uninstallation
php artisan world:uninstall --cities --force
```

## Questions?

-   Open an issue for bugs or feature requests
-   Start a discussion for questions
-   Check existing issues before creating new ones

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
