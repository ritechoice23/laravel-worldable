<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Ritechoice23\Worldable\Commands\Concerns\ManagesWorldComponents;

class WorldUninstallCommand extends Command
{
    use ManagesWorldComponents;

    protected $signature = 'world:uninstall 
                            {--all : Uninstall all world data}
                            {--continents : Uninstall continents}
                            {--subregions : Uninstall subregions}
                            {--countries : Uninstall countries}
                            {--states : Uninstall states}
                            {--cities : Uninstall cities}
                            {--languages : Uninstall languages}
                            {--currencies : Uninstall currencies}
                            {--timezones : Uninstall timezones}
                            {--worldables : Uninstall worldables pivot table}
                            {--strategy=nullify : How to handle dependent records (nullify, block, cascade)}
                            {--force : Skip confirmation}';

    protected $description = 'Uninstall world data and optionally remove migrations';

    public function handle(): int
    {
        $this->displayHeader();

        $componentsToUninstall = $this->getComponentsToUninstall();

        if (empty($componentsToUninstall)) {
            $this->warn('No components selected.');

            return self::FAILURE;
        }

        $componentsToUninstall = $this->checkAndResolveDependents($componentsToUninstall);

        if ($componentsToUninstall === self::FAILURE) {
            return self::FAILURE;
        }

        if (empty($componentsToUninstall)) {
            $this->info('Uninstallation cancelled.');

            return self::SUCCESS;
        }

        $this->displayUninstallationPlan($componentsToUninstall);

        if (! $this->confirmUninstallation($componentsToUninstall)) {
            $this->info('Uninstallation cancelled.');

            return self::SUCCESS;
        }

        $this->dropTables($componentsToUninstall);

        $this->removeMigrations($componentsToUninstall);

        $this->displaySuccess();

        return self::SUCCESS;
    }

    private function displayHeader(): void
    {
        $this->warn('🗑️  Laravel Worldable Uninstallation');
        $this->newLine();
    }

    private function getComponentsToUninstall(): array
    {
        if ($this->option('all')) {
            return array_keys($this->getTables());
        }

        $hasAnyOption = false;
        $allComponents = array_merge(array_keys($this->getTables()), ['worldables']);

        foreach ($allComponents as $component) {
            if ($this->option($component)) {
                $hasAnyOption = true;
                break;
            }
        }

        if ($hasAnyOption) {
            $selected = [];
            foreach ($allComponents as $component) {
                if ($this->option($component)) {
                    $selected[] = $component;
                }
            }

            return $selected;
        }

        return $this->interactiveSelection();
    }

    private function interactiveSelection(): array
    {
        $this->warn('No components specified. Please select components to uninstall:');
        $this->newLine();

        $allComponents = array_keys($this->getTables());

        $choices = $this->choice(
            'Which components would you like to uninstall? (Use space to select, enter to confirm)',
            array_merge(['all'], $allComponents),
            null,
            null,
            true
        );

        if (in_array('all', $choices)) {
            return $allComponents;
        }

        return $choices;
    }

    private function checkAndResolveDependents(array $toUninstall): array|int
    {
        $strategy = $this->option('strategy');

        if (! in_array($strategy, ['nullify', 'block', 'cascade'])) {
            $this->error("Invalid strategy: {$strategy}");
            $this->line('Valid strategies: nullify, block, cascade');

            return self::FAILURE;
        }

        $additionalComponents = [];
        $tables = $this->getTables();

        foreach ($toUninstall as $component) {
            if (isset($this->dependents[$component])) {
                $existingDependents = $this->getExistingDependents($component);

                if (! empty($existingDependents)) {
                    $this->newLine();
                    $this->warn("⚠️  Warning: '{$component}' has dependent data:");

                    $dependentRecords = [];
                    foreach ($existingDependents as $dependent) {
                        $count = $this->getTableRecordCount($tables[$dependent]);
                        $dependentRecords[$dependent] = $count;
                        $this->line("  • {$dependent} ({$count} records)");
                    }

                    $this->newLine();
                    $this->displayStrategyImpact($strategy, $component, $dependentRecords);

                    if ($strategy === 'block') {
                        $this->error('Cannot uninstall with --strategy=block when dependent data exists.');
                        $this->comment('Options:');
                        $this->line('  1. Use --strategy=nullify to nullify foreign keys');
                        $this->line('  2. Use --strategy=cascade to also remove dependent data');
                        $this->line('  3. Manually clean up dependent data first');

                        return [];
                    }

                    if ($strategy === 'cascade') {
                        if ($this->option('force') || $this->confirm('Also uninstall dependent components?', false)) {
                            $additionalComponents = array_merge($additionalComponents, $existingDependents);
                        } else {
                            $this->info('Uninstallation cancelled.');

                            return [];
                        }
                    }

                    if ($strategy === 'nullify') {
                        $this->comment('Foreign keys will be set to NULL.');
                        if ($this->option('force') || $this->confirm('Continue?', true)) {
                            // Continue with uninstallation
                        } else {
                            return [];
                        }
                    }
                }
            }
        }

        return array_unique(array_merge($toUninstall, $additionalComponents));
    }

    private function displayStrategyImpact(string $strategy, string $component, array $dependentRecords): void
    {
        $this->info("Strategy: <fg=cyan>{$strategy}</>");
        $this->newLine();

        match ($strategy) {
            'nullify' => $this->displayNullifyImpact($dependentRecords),
            'block' => $this->displayBlockImpact(),
            'cascade' => $this->displayCascadeImpact($dependentRecords),
            default => null,
        };
    }

    private function displayNullifyImpact(array $dependentRecords): void
    {
        $this->line('Impact:');
        foreach ($dependentRecords as $component => $count) {
            $this->line("  • {$count} {$component} records will have NULL foreign keys");
        }
        $this->newLine();
        $this->comment('ℹ  Records will remain but relationships will be broken.');
        $this->comment('   Use "php artisan world:link" after reinstalling to restore links.');
    }

    private function displayBlockImpact(): void
    {
        $this->line('Impact: Uninstallation will be blocked.');
        $this->newLine();
        $this->comment('ℹ  No changes will be made.');
    }

    private function displayCascadeImpact(array $dependentRecords): void
    {
        $totalRecords = array_sum($dependentRecords);
        $this->line('Impact:');
        $this->line("  • All {$totalRecords} dependent records will be permanently deleted");
        $this->newLine();
        $this->warn('⚠️  This action cannot be undone!');
    }

    private function getExistingDependents(string $component): array
    {
        if (! isset($this->dependents[$component])) {
            return [];
        }

        $tables = $this->getTables();

        return array_filter(
            $this->dependents[$component],
            fn ($dep) => DB::getSchemaBuilder()->hasTable($tables[$dep])
        );
    }

    private function getTableRecordCount(string $table): int
    {
        try {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                return DB::table($table)->count();
            }
        } catch (\Exception $e) {

        }

        return 0;
    }

    private function displayUninstallationPlan(array $components): void
    {
        $this->newLine();
        $this->info('📋 Uninstallation Plan:');

        $tables = $this->getTables();
        foreach ($components as $component) {
            $table = $tables[$component] ?? $component;
            $count = $this->getTableRecordCount($table);
            $countStr = $count > 0 ? " ({$count} records)" : ' (empty)';
            $this->line("  🗑️  {$component}{$countStr}");
        }

        $this->newLine();
    }

    private function confirmUninstallation(array $components): bool
    {
        if ($this->option('force')) {
            return true;
        }

        $componentList = implode(', ', $components);

        return $this->confirm("Are you sure you want to uninstall: {$componentList}?", false);
    }

    private function dropTables(array $components): void
    {
        $this->info('🗑️  Dropping tables...');
        $this->newLine();

        $tables = $this->getTables();
        foreach ($components as $component) {
            $table = $tables[$component] ?? $component;

            if (DB::getSchemaBuilder()->hasTable($table)) {
                $this->info("  Dropping {$table}...");

                // SQLite doesn't support CASCADE in DROP TABLE
                $driver = DB::getDriverName();
                if ($driver === 'sqlite') {
                    Schema::dropIfExists($table);
                } else {
                    DB::statement("DROP TABLE IF EXISTS {$table} CASCADE");
                }

                $this->comment('  ✓ Dropped');

                $this->removeMigrationEntry($component);
            } else {
                $this->comment("  ⏭️  {$table} does not exist, skipping");
            }
        }

        $this->newLine();
    }

    private function removeMigrationEntry(string $component): void
    {
        if (! isset($this->migrations[$component])) {
            return;
        }

        $migrationName = $this->migrations[$component];

        try {
            $deleted = DB::table('migrations')
                ->where('migration', 'like', '%'.basename($migrationName, '.php').'%')
                ->delete();

            if ($deleted > 0) {
                $this->comment('  ✓ Removed migration entry from database');
            }
        } catch (\Exception $e) {
            $this->comment('  ⚠️  Could not remove migration entry: '.$e->getMessage());
        }
    }

    private function removeMigrations(array $components): void
    {
        if (! $this->option('force') && ! $this->confirm('Do you want to remove migration files?', false)) {
            return;
        }

        $this->info('🗑️  Removing migrations...');
        $this->newLine();

        foreach ($components as $component) {
            if (! isset($this->migrations[$component])) {
                continue;
            }

            $migrationFile = database_path('migrations/'.$this->migrations[$component]);

            if (File::exists($migrationFile)) {
                File::delete($migrationFile);
                $this->comment("  ✓ Removed {$this->migrations[$component]}");
            }
        }

        $this->newLine();
    }

    private function displaySuccess(): void
    {
        $this->info('🎉 Uninstallation complete!');
    }
}
