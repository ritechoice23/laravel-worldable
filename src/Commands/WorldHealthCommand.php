<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ritechoice23\Worldable\Commands\Concerns\ManagesWorldComponents;

class WorldHealthCommand extends Command
{
    use ManagesWorldComponents;

    protected $signature = 'world:health 
                            {--detailed : Show detailed information including orphaned record examples}
                            {--json : Output results in JSON format}';

    protected $description = 'Check the health and status of world data components';

    public function handle(): int
    {
        if ($this->option('json')) {
            return $this->handleJsonOutput();
        }

        $this->displayHeader();

        $components = $this->getComponentsStatus();

        $this->displayComponentsTable($components);
        $this->newLine();

        $orphanCounts = $this->getOrphanCounts();
        $this->displayOrphanStatus($orphanCounts);

        if ($this->option('detailed') && array_sum($orphanCounts) > 0) {
            $this->newLine();
            $this->displayOrphanExamples($orphanCounts);
        }
        $this->newLine();
        $this->displayRecommendations($components, $orphanCounts);

        return self::SUCCESS;
    }

    private function handleJsonOutput(): int
    {
        $components = $this->getComponentsStatus();
        $orphanCounts = $this->getOrphanCounts();

        $output = [
            'timestamp' => now()->toIso8601String(),
            'components' => $components,
            'orphans' => $orphanCounts,
            'total_records' => array_sum(array_column($components, 'count')),
            'total_orphans' => array_sum($orphanCounts),
            'health_score' => $this->calculateHealthScore($components, $orphanCounts),
        ];

        $this->line(json_encode($output, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }

    private function displayHeader(): void
    {
        $this->info('🏥 World Data Health Check');
        $this->newLine();
    }

    private function getComponentsStatus(): array
    {
        $status = [];

        foreach ($this->getComponentList() as $component) {
            $tableName = config("worldable.tables.{$component}", "world_{$component}");

            if (Schema::hasTable($tableName)) {
                $count = DB::table($tableName)->count();
                $status[$component] = [
                    'installed' => true,
                    'count' => $count,
                    'table' => $tableName,
                ];
            } else {
                $status[$component] = [
                    'installed' => false,
                    'count' => 0,
                    'table' => $tableName,
                ];
            }
        }

        return $status;
    }

    private function displayComponentsTable(array $components): void
    {
        $this->info('📊 Component Status:');
        $this->newLine();

        $headers = ['Component', 'Status', 'Records', 'Table'];
        $rows = [];

        foreach ($components as $name => $data) {
            $status = $data['installed']
                ? '<fg=green>✓ Installed</>'
                : '<fg=red>✗ Not Installed</>';

            $count = $data['installed']
                ? number_format($data['count'])
                : '-';

            $rows[] = [
                ucfirst($name),
                $status,
                $count,
                $data['table'],
            ];
        }

        $this->table($headers, $rows);
    }

    private function getOrphanCounts(): array
    {
        $orphans = [
            'subregions' => 0,
            'countries_continent' => 0,
            'countries_subregion' => 0,
            'states' => 0,
            'cities_country' => 0,
            'cities_state' => 0,
        ];

        $subregionsTable = config('worldable.tables.subregions', 'world_subregions');
        if (Schema::hasTable($subregionsTable)) {
            $orphans['subregions'] = DB::table($subregionsTable)
                ->whereNull('continent_id')
                ->count();
        }

        $countriesTable = config('worldable.tables.countries', 'world_countries');
        if (Schema::hasTable($countriesTable)) {
            $orphans['countries_continent'] = DB::table($countriesTable)
                ->whereNull('continent_id')
                ->count();

            $orphans['countries_subregion'] = DB::table($countriesTable)
                ->whereNull('subregion_id')
                ->count();
        }

        $statesTable = config('worldable.tables.states', 'world_states');
        if (Schema::hasTable($statesTable)) {
            $orphans['states'] = DB::table($statesTable)
                ->whereNull('country_id')
                ->count();
        }

        $citiesTable = config('worldable.tables.cities', 'world_cities');
        if (Schema::hasTable($citiesTable)) {
            $orphans['cities_country'] = DB::table($citiesTable)
                ->whereNull('country_id')
                ->count();

            $orphans['cities_state'] = DB::table($citiesTable)
                ->whereNull('state_id')
                ->count();
        }

        return $orphans;
    }

    private function displayOrphanStatus(array $orphanCounts): void
    {
        $totalOrphans = array_sum($orphanCounts);

        if ($totalOrphans === 0) {
            $this->info('✓ All relationships are properly linked!');

            return;
        }

        $this->warn("⚠️  Found {$totalOrphans} orphaned relationships");
        $this->newLine();

        $labels = [
            'subregions' => 'Subregions without continents',
            'countries_continent' => 'Countries without continents',
            'countries_subregion' => 'Countries without subregions',
            'states' => 'States without countries',
            'cities_country' => 'Cities without countries',
            'cities_state' => 'Cities without states',
        ];

        foreach ($orphanCounts as $key => $count) {
            if ($count > 0) {
                $this->line("  • {$labels[$key]}: <fg=yellow>".number_format($count).'</>');
            }
        }
    }

    private function displayOrphanExamples(array $orphanCounts): void
    {
        $this->info('📋 Sample Orphaned Records:');
        $this->newLine();

        if ($orphanCounts['subregions'] > 0) {
            $this->displaySampleOrphans(
                'world_subregions',
                'continent_id',
                'Subregions without continents',
                ['name', 'code']
            );
        }

        if ($orphanCounts['countries_continent'] > 0) {
            $this->displaySampleOrphans(
                'world_countries',
                'continent_id',
                'Countries without continents',
                ['name', 'iso_code']
            );
        }

        if ($orphanCounts['states'] > 0) {
            $this->displaySampleOrphans(
                'world_states',
                'country_id',
                'States without countries',
                ['name', 'code']
            );
        }

        if ($orphanCounts['cities_country'] > 0) {
            $this->displaySampleOrphans(
                'world_cities',
                'country_id',
                'Cities without countries',
                ['name']
            );
        }
    }

    private function displaySampleOrphans(string $table, string $nullColumn, string $title, array $columns): void
    {
        $tableName = config("worldable.tables.{$table}", $table);

        if (! Schema::hasTable($tableName)) {
            return;
        }

        $samples = DB::table($tableName)
            ->whereNull($nullColumn)
            ->limit(5)
            ->get($columns);

        if ($samples->isEmpty()) {
            return;
        }

        $this->comment($title.':');

        foreach ($samples as $sample) {
            $values = [];
            foreach ($columns as $col) {
                if (isset($sample->$col)) {
                    $values[] = $sample->$col;
                }
            }
            $this->line('  - '.implode(' | ', $values));
        }

        $this->newLine();
    }

    private function displayRecommendations(array $components, array $orphanCounts): void
    {
        $recommendations = [];

        $totalOrphans = array_sum($orphanCounts);

        if ($totalOrphans > 0) {
            $recommendations[] = [
                'icon' => '🔗',
                'message' => 'Run "php artisan world:link" to establish missing relationships',
            ];
        }

        $notInstalled = array_filter($components, fn ($c) => ! $c['installed']);
        if (! empty($notInstalled)) {
            $recommendations[] = [
                'icon' => '📦',
                'message' => 'Install missing components with "php artisan world:install"',
            ];
        }

        if (empty($recommendations)) {
            $this->info('✓ No recommendations. Your world data is in excellent shape!');

            return;
        }

        $this->info('💡 Recommendations:');
        $this->newLine();

        foreach ($recommendations as $rec) {
            $this->line("  {$rec['icon']} {$rec['message']}");
        }
    }

    private function calculateHealthScore(array $components, array $orphanCounts): float
    {
        $installedCount = count(array_filter($components, fn ($c) => $c['installed']));
        $totalComponents = count($components);

        $installScore = ($installedCount / $totalComponents) * 50;

        $totalRecords = array_sum(array_column($components, 'count'));
        $totalOrphans = array_sum($orphanCounts);

        $linkScore = $totalRecords > 0
            ? (($totalRecords - $totalOrphans) / $totalRecords) * 50
            : 0;

        return round($installScore + $linkScore, 2);
    }
}
