<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Ritechoice23\Worldable\Commands\Concerns\ManagesWorldComponents;

class WorldInstallCommand extends Command
{
    use ManagesWorldComponents;

    protected $signature = 'world:install 
                            {--all : Install all world data}
                            {--skip-large : Skip large datasets (cities, states)}
                            {--rollback-on-error : Rollback migrations if seeding fails}
                            {--with-dependencies : Automatically install required dependencies}
                            {--auto-link : Automatically link relationships after installation}
                            {--no-link : Skip relationship linking prompt}
                            {--continents : Install continents}
                            {--subregions : Install subregions}
                            {--countries : Install countries}
                            {--states : Install states}
                            {--cities : Install cities}
                            {--languages : Install languages}
                            {--currencies : Install currencies}
                            {--timezones : Install timezones}
                            {--worldables : Install worldables polymorphic pivot table}';

    protected $description = 'Install world data (continents, countries, states, cities, etc.)';

    public function handle(): int
    {
        $this->displayHeader();

        $componentsToInstall = $this->getComponentsToInstall();

        if (empty($componentsToInstall)) {
            $this->warn('No components selected.');

            return self::FAILURE;
        }

        $this->displayInstallationPlan($componentsToInstall);

        if (! $this->publishAndRunMigrations($componentsToInstall)) {
            return self::FAILURE;
        }

        if (! $this->seedComponents($componentsToInstall)) {
            if ($this->option('rollback-on-error')) {
                $this->handleRollback();
            }

            return self::FAILURE;
        }

        // Auto-link relationships if beneficial
        $this->handleAutoLinking($componentsToInstall);

        $this->displaySuccess();

        return self::SUCCESS;
    }

    private function displayHeader(): void
    {
        $this->info('🌍 Worldable Installation');
        $this->newLine();
    }

    /**
     * @param  array<string, bool>  $installedComponents
     */
    private function handleAutoLinking(array $installedComponents): void
    {
        // Skip if explicitly told not to link
        if ($this->option('no-link')) {
            return;
        }

        // Check if linking would be beneficial
        $linkableComponents = $this->getLinkableComponents($installedComponents);

        if (empty($linkableComponents)) {
            return;
        }

        $this->newLine();
        $this->info('🔗 Relationship Linking');
        $this->newLine();

        // Show which components can be linked
        $this->comment('The following components have dependencies that can be linked:');
        foreach (array_keys($linkableComponents) as $component) {
            $deps = $this->dependencies[$component] ?? [];
            $installedDeps = array_filter($deps, fn ($dep) => isset($installedComponents[$dep]));
            if (! empty($installedDeps)) {
                $this->line("  • {$component} → ".implode(', ', $installedDeps));
            }
        }
        $this->newLine();

        // Auto-link or prompt
        $shouldLink = false;

        if ($this->option('auto-link')) {
            $shouldLink = true;
            $this->info('Auto-linking enabled...');
        } elseif ($this->confirm('Would you like to link relationships now?', true)) {
            $shouldLink = true;
        }

        if ($shouldLink) {
            $this->newLine();
            $exitCode = $this->call('world:link', [
                '--component' => array_keys($linkableComponents),
            ]);

            if ($exitCode === 0) {
                $this->newLine();
                $this->info('✓ Relationships linked successfully');
            }
        } else {
            $this->comment('ℹ  You can link relationships later with: php artisan world:link');
        }
    }

    /**
     * Get components that have linkable dependencies in the installed set
     *
     * @param  array<string, bool>  $installedComponents
     * @return array<string, bool>
     */
    private function getLinkableComponents(array $installedComponents): array
    {
        $linkable = [];

        foreach (array_keys($installedComponents) as $component) {
            // Check if this component has dependencies
            if (! isset($this->dependencies[$component])) {
                continue;
            }

            // Check if any dependencies were also installed
            $deps = $this->dependencies[$component];
            $hasInstalledDep = false;

            foreach ($deps as $dep) {
                if (isset($installedComponents[$dep])) {
                    $hasInstalledDep = true;
                    break;
                }
            }

            if ($hasInstalledDep) {
                $linkable[$component] = $installedComponents[$component];
            }
        }

        return $linkable;
    }

    private function getComponentsToInstall(): array
    {
        if ($this->option('all')) {
            $components = $this->seeders;

            if ($this->option('skip-large')) {
                foreach ($this->largeDatasets as $large) {
                    unset($components[$large]);
                }
            }

            return $components;
        }

        $hasAnyOption = false;
        foreach ($this->getComponentList() as $component) {
            if ($this->option($component)) {
                $hasAnyOption = true;
                break;
            }
        }

        if ($hasAnyOption) {
            $selected = [];
            foreach ($this->seeders as $component => $seeder) {
                if ($this->option($component)) {
                    $selected[$component] = $seeder;
                }
            }

            if ($this->option('with-dependencies')) {
                return $this->resolveDependencies($selected);
            }

            $this->showDependencyWarnings($selected);

            return $selected;
        }

        return $this->interactiveSelection();
    }

    private function interactiveSelection(): array
    {
        $this->warn('No components specified. Please select components to install:');
        $this->newLine();

        $choices = $this->choice(
            'Which components would you like to install? (Use space to select, enter to confirm)',
            array_merge(['all'], $this->getComponentList()),
            null,
            null,
            true
        );

        if (in_array('all', $choices)) {
            $components = $this->seeders;

            if ($this->confirm('Include large datasets (cities, states)? This may take several minutes.', false)) {
                return $components;
            }

            foreach ($this->largeDatasets as $large) {
                unset($components[$large]);
            }

            return $components;
        }

        $selected = [];
        foreach ($choices as $choice) {
            $selected[$choice] = $this->seeders[$choice] ?? null;
        }

        if ($this->option('with-dependencies')) {
            return $this->resolveDependencies($selected);
        }

        $this->showDependencyWarnings($selected);

        return $selected;
    }

    private function showDependencyWarnings(array $selected): void
    {
        $missingDeps = [];

        foreach (array_keys($selected) as $component) {
            if (isset($this->dependencies[$component])) {
                foreach ($this->dependencies[$component] as $dependency) {
                    if (! isset($selected[$dependency])) {
                        $missingDeps[$component][] = $dependency;
                    }
                }
            }
        }

        if (! empty($missingDeps)) {
            $this->newLine();
            $this->warn('⚠️  Warning: Installing components without their dependencies');
            $this->newLine();

            foreach ($missingDeps as $component => $deps) {
                $this->line("  • <fg=yellow>{$component}</> works best with: <fg=cyan>".implode(', ', $deps).'</>');
            }

            $this->newLine();
            $this->comment('ℹ  Records will be created with NULL foreign keys.');
            $this->comment('   You can link them later with: php artisan world:link');
            $this->comment('   Or use --with-dependencies flag to auto-include dependencies.');
            $this->newLine();
        }
    }

    private function resolveDependencies(array $selected): array
    {
        $resolved = [];
        $addedDependencies = [];

        foreach (array_keys($selected) as $component) {
            if (isset($this->dependencies[$component])) {
                foreach ($this->dependencies[$component] as $dependency) {
                    if (! isset($resolved[$dependency]) && isset($this->seeders[$dependency])) {
                        $resolved[$dependency] = $this->seeders[$dependency];
                        $addedDependencies[] = $dependency;
                    }
                }
            }

            $resolved[$component] = $selected[$component];
        }

        if (! empty($addedDependencies)) {
            $this->newLine();
            $this->comment('📦 Adding required dependencies:');
            foreach ($addedDependencies as $dep) {
                $this->line("  • {$dep}");
            }
        }

        return $resolved;
    }

    private function displayInstallationPlan(array $components): void
    {
        $this->newLine();
        $this->info('📋 Installation Plan:');
        foreach (array_keys($components) as $component) {
            $icon = $this->isLargeDataset($component) ? '⚠️ ' : '✓';
            $this->line("  {$icon} {$component}");
        }
        $this->newLine();
    }

    private function publishAndRunMigrations(array $components): bool
    {
        $this->info('📦 Publishing migrations...');

        foreach (array_keys($components) as $component) {
            if (isset($this->migrations[$component])) {
                $this->publishMigration($this->migrations[$component]);
            }
        }

        $this->newLine();
        $this->info('🔄 Running migrations...');

        $exitCode = $this->call('migrate');

        if ($exitCode !== 0) {
            $this->error('Migration failed.');

            return false;
        }

        $this->verifyTablesExist($components);

        $this->newLine();

        return true;
    }

    private function verifyTablesExist(array $components): void
    {
        $tables = $this->getTables();
        $missingTables = [];

        foreach (array_keys($components) as $component) {
            $tableName = $tables[$component] ?? null;

            if ($tableName && ! DB::getSchemaBuilder()->hasTable($tableName)) {
                $missingTables[] = $tableName;
            }
        }

        if (! empty($missingTables)) {
            $this->newLine();
            $this->warn('⚠️  The following tables do not exist and will be created manually:');

            foreach ($missingTables as $table) {
                $this->comment("  - {$table}");
            }

            foreach (array_keys($components) as $component) {
                $tableName = $tables[$component] ?? null;

                if ($tableName && in_array($tableName, $missingTables)) {
                    $this->createTableManually($component, $tableName);
                }
            }
        }
    }

    private function createTableManually(string $component, string $tableName): void
    {
        if (! isset($this->migrations[$component])) {
            return;
        }

        $migrationFile = __DIR__.'/../../database/migrations/'.$this->migrations[$component];

        if (! file_exists($migrationFile)) {
            return;
        }

        try {
            require_once $migrationFile;

            $migrationClass = require $migrationFile;

            if (is_object($migrationClass) && method_exists($migrationClass, 'up')) {
                $this->comment("  ✓ Creating {$tableName}...");
                $migrationClass->up();
            }
        } catch (\Exception $e) {
            $this->error("  ✗ Failed to create {$tableName}: ".$e->getMessage());
        }
    }

    private function publishMigration(string $migrationFile): void
    {
        $source = __DIR__.'/../../database/migrations/'.$migrationFile;
        $destination = database_path('migrations/'.$migrationFile);

        if (File::exists($destination)) {
            File::delete($destination);
        }

        File::copy($source, $destination);
        $this->comment("  ✓ {$migrationFile}");
    }

    private function seedComponents(array $components): bool
    {
        $hasDataToSeed = array_filter($components, fn ($seeder) => $seeder !== null);

        if (! empty($hasDataToSeed)) {
            $this->info('🌱 Seeding world data...');
            $this->newLine();
        }

        $failed = false;

        foreach ($components as $component => $seeder) {
            if ($seeder === null) {
                $this->info("✅ {$component} migration published (no data to seed)");
                $this->newLine();

                continue;
            }

            if (! $this->seedComponent($component, $seeder)) {
                $failed = true;
                if ($this->option('rollback-on-error')) {
                    break;
                }
            }
        }

        return ! $failed;
    }

    private function seedComponent(string $component, string $seeder): bool
    {
        $this->info("📦 Installing {$component}...");

        try {
            $seederPath = __DIR__.'/../../database/seeders/'.$seeder.'.php';

            if (! file_exists($seederPath)) {
                throw new \Exception("Seeder file not found: {$seederPath}");
            }

            require_once $seederPath;

            $seederClass = "Ritechoice23\\Worldable\\Database\\Seeders\\{$seeder}";

            if (! class_exists($seederClass)) {
                $seederClass = "Database\\Seeders\\{$seeder}";
            }

            $seederInstance = new $seederClass;

            if (method_exists($seederInstance, 'setCommand')) {
                $seederInstance->setCommand($this);
            }

            $seederInstance->run();

            $this->info("✅ {$component} installed successfully");
            $this->newLine();

            return true;
        } catch (\Exception $e) {
            $this->error("❌ Failed to install {$component}: ".$e->getMessage());
            $this->newLine();

            return false;
        }
    }

    private function handleRollback(): void
    {
        $this->newLine();
        $this->error('🔄 Rolling back migrations due to seeding failure...');
        $this->call('migrate:rollback');
        $this->warn('Migrations have been rolled back.');
    }

    private function displaySuccess(): void
    {
        $this->newLine();
        $this->info('🎉 World data installation complete!');
    }
}
