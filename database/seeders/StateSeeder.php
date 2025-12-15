<?php

namespace Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class StateSeeder extends Seeder
{
    private const CHUNK_SIZE = 500;

    private const HTTP_TIMEOUT = 30;

    /**
     * The command instance for output.
     */
    protected $command;

    /**
     * Set the command instance.
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    public function run(): void
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $url = 'https://raw.githubusercontent.com/ritechoice23/countries-states-cities-database/master/json/states.json';

        $this->command->info('📥 Downloading states data from GitHub...');
        $this->command->newLine();

        $response = Http::timeout(self::HTTP_TIMEOUT)->get($url);

        if (! $response->successful()) {
            $this->command->error('Failed to fetch states data from GitHub.');

            return;
        }

        $downloadSize = strlen($response->body());
        $this->command->info('✓ Downloaded '.number_format($downloadSize / 1024, 2).' KB');
        $this->command->newLine();

        $jsonData = $response->body();

        // Count states for progress bar
        $totalCount = substr_count($jsonData, '"name"');
        $this->command->info('📊 Processing approximately '.number_format($totalCount).' states...');
        $this->command->newLine();

        $countriesTable = config('worldable.tables.countries', 'world_countries');
        $countriesInstalled = Schema::hasTable($countriesTable) && DB::table($countriesTable)->exists();
        $countryMap = $countriesInstalled ? $this->buildCountryMap() : [];

        $bar = $this->command->getOutput()->createProgressBar($totalCount);
        $bar->start();

        $statesTable = config('worldable.tables.states', 'world_states');
        $inserted = 0;
        $orphaned = 0;
        $batch = [];

        // Stream parse the JSON
        $currentState = '';
        $braceDepth = 0;
        $inArray = false;

        for ($i = 0; $i < strlen($jsonData); $i++) {
            $char = $jsonData[$i];

            if ($char === '[' && ! $inArray) {
                $inArray = true;

                continue;
            }

            if ($inArray) {
                if ($char === '{') {
                    if ($braceDepth === 0) {
                        $currentState = '{';
                    } else {
                        $currentState .= $char;
                    }
                    $braceDepth++;
                } elseif ($char === '}') {
                    $braceDepth--;
                    $currentState .= $char;

                    if ($braceDepth === 0) {
                        $state = json_decode($currentState, true);

                        if (is_array($state) && ! empty($state['name'])) {
                            $countryId = $countryMap[$state['country_code']] ?? null;

                            $batch[] = [
                                'country_id' => $countryId,
                                'name' => $state['name'],
                                'code' => $state['state_code'] ?? null,
                                'metadata' => json_encode([
                                    'latitude' => $state['latitude'] ?? null,
                                    'longitude' => $state['longitude'] ?? null,
                                    'type' => $state['type'] ?? null,
                                    'country_code' => $state['country_code'] ?? null,
                                    'country_name' => $state['country_name'] ?? null,
                                ], JSON_THROW_ON_ERROR),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            if ($countryId) {
                                $inserted++;
                            } else {
                                $orphaned++;
                            }

                            if (count($batch) >= self::CHUNK_SIZE) {
                                DB::table($statesTable)->insertOrIgnore($batch);
                                $batch = [];
                            }

                            $bar->advance();
                        }

                        $currentState = '';
                    }
                } else {
                    if ($braceDepth > 0) {
                        $currentState .= $char;
                    }
                }
            }
        }

        if (! empty($batch)) {
            DB::table($statesTable)->insertOrIgnore($batch);
        }

        unset($jsonData);

        $bar->finish();
        $this->command->newLine(2);

        $this->command->info('✓ Seeded '.($inserted + $orphaned).' states successfully.');

        if ($orphaned > 0) {
            $this->command->warn("⚠ {$orphaned} states without country links (countries not installed).");
            $this->command->info("ℹ Run 'php artisan world:link' after installing countries to establish relationships.");
        }
    }

    private function buildCountryMap(): array
    {
        $countriesTable = config('worldable.tables.countries', 'world_countries');

        return DB::table($countriesTable)
            ->pluck('id', 'iso_code')
            ->toArray();
    }
}
