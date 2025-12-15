<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Commands;

use Illuminate\Console\Command;
use Ritechoice23\Worldable\Commands\Concerns\ManagesWorldComponents;
use Ritechoice23\Worldable\Linkers\LinkerResolver;

class WorldLinkCommand extends Command
{
    use ManagesWorldComponents;

    protected $signature = 'world:link 
                            {--component= : Specific component to link (subregions, countries, states, cities)}
                            {--dry-run : Show what would be linked without making changes}
                            {--force : Link records even if they already have relationships}';

    protected $description = 'Link orphaned world records to their parent relationships using metadata';

    public function handle(LinkerResolver $resolver): int
    {
        $this->displayHeader();

        $component = $this->option('component');
        $isDryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        // Ensure component is a string or null
        if (is_array($component)) {
            $component = null;
        }

        if ($component) {
            if (! $resolver->has($component)) {
                $this->error("Invalid component: {$component}");
                $this->line('Valid components: '.implode(', ', $resolver->getAvailableComponents()));

                return self::FAILURE;
            }

            $this->linkComponent($resolver, $component, $isDryRun, $force);
        } else {
            foreach ($resolver->getAvailableComponents() as $comp) {
                $this->linkComponent($resolver, $comp, $isDryRun, $force);
            }
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info('✓ Dry run completed. Use without --dry-run to apply changes.');
        } else {
            $this->info('✓ Linking completed successfully!');
            $this->comment('ℹ  Run "php artisan world:health" to verify relationships.');
        }

        return self::SUCCESS;
    }

    private function displayHeader(): void
    {
        $this->info('🔗 World Data Linking');
        $this->newLine();
    }

    private function linkComponent(LinkerResolver $resolver, string $component, bool $isDryRun, bool $force): void
    {
        $linker = $resolver->resolve($component);

        if (! $linker) {
            $this->warn("No linker found for component: {$component}");

            return;
        }

        $outputStyle = new \Symfony\Component\Console\Style\SymfonyStyle(
            $this->input,
            $this->output
        );

        $linker->setOutput($outputStyle);
        $result = $linker->link($isDryRun, $force);

        // Results are already displayed by the linker
        // Additional summary could be added here if needed
    }
}
