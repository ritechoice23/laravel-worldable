<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Commands\Concerns;

trait ManagesWorldComponents
{
    /**
     * @var array<string, string|null>
     */
    protected array $seeders = [
        'continents' => 'ContinentSeeder',
        'subregions' => 'SubregionSeeder',
        'countries' => 'CountrySeeder',
        'states' => 'StateSeeder',
        'cities' => 'CitySeeder',
        'languages' => 'LanguageSeeder',
        'currencies' => 'CurrencySeeder',
        'timezones' => 'TimezoneSeeder',
        'worldables' => null,
    ];

    /**
     * @var array<string, string>
     */
    protected array $migrations = [
        'continents' => '2025_12_02_000000_create_world_continents_table.php',
        'subregions' => '2025_12_02_000001_create_world_subregions_table.php',
        'countries' => '2025_12_02_000002_create_world_countries_table.php',
        'states' => '2025_12_02_000003_create_world_states_table.php',
        'cities' => '2025_12_02_000004_create_world_cities_table.php',
        'languages' => '2025_12_02_000005_create_world_languages_table.php',
        'currencies' => '2025_12_02_000006_create_world_currencies_table.php',
        'worldables' => '2025_12_02_000007_create_worldables_table.php',
        'timezones' => '2025_12_02_000008_create_world_timezones_table.php',
    ];

    /**
     * @return array<string, string>
     */
    protected function getTables(): array
    {
        return [
            'continents' => (string) config('worldable.tables.continents', 'world_continents'),
            'subregions' => (string) config('worldable.tables.subregions', 'world_subregions'),
            'countries' => (string) config('worldable.tables.countries', 'world_countries'),
            'states' => (string) config('worldable.tables.states', 'world_states'),
            'cities' => (string) config('worldable.tables.cities', 'world_cities'),
            'languages' => (string) config('worldable.tables.languages', 'world_languages'),
            'currencies' => (string) config('worldable.tables.currencies', 'world_currencies'),
            'timezones' => (string) config('worldable.tables.timezones', 'world_timezones'),
            'worldables' => (string) config('worldable.tables.worldables', 'worldables'),
        ];
    }

    /**
     * @var array<string, array<int, string>>
     */
    protected array $dependencies = [
        'subregions' => ['continents'],
        'countries' => ['continents', 'subregions'],
        'states' => ['continents', 'subregions', 'countries'],
        'cities' => ['continents', 'subregions', 'countries', 'states'],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    protected array $dependents = [
        'continents' => ['subregions', 'countries', 'states', 'cities'],
        'subregions' => ['countries', 'states', 'cities'],
        'countries' => ['states', 'cities'],
        'states' => ['cities'],
    ];

    /**
     * @var array<int, string>
     */
    protected array $largeDatasets = ['cities', 'states'];

    /**
     * @return array<int, string>
     */
    protected function getComponentList(): array
    {
        return array_keys($this->seeders);
    }

    protected function formatComponentName(string $component): string
    {
        return ucfirst(str_replace('_', ' ', $component));
    }

    protected function isLargeDataset(string $component): bool
    {
        return in_array($component, $this->largeDatasets);
    }
}
