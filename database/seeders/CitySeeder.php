<?php

namespace Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class CitySeeder extends Seeder
{
    private const CHUNK_SIZE = 1000;

    private const HTTP_TIMEOUT = 180;

    private const MEMORY_LIMIT = '1024M';

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
        ini_set('memory_limit', self::MEMORY_LIMIT);
        set_time_limit(0);

        $url = 'https://raw.githubusercontent.com/ritechoice23/countries-states-cities-database/master/json/cities.json.gz';

        $this->command?->info('📥 Downloading cities data from GitHub...');
        $this->command?->comment('   This may take a while depending on your connection speed.');
        $this->command?->newLine();

        try {
            $response = Http::timeout(self::HTTP_TIMEOUT)->get($url);

            if (! $response->successful()) {
                $this->command?->error('Failed to fetch cities data from GitHub. HTTP Status: '.$response->status());

                return;
            }

            $downloadSize = strlen($response->body());
            $this->command?->info('✓ Downloaded '.number_format($downloadSize / 1024 / 1024, 2).' MB (compressed)');
            $this->command?->newLine();

            $this->command?->info('📦 Decompressing cities data...');

            $decompressed = gzdecode($response->body());

            if ($decompressed === false) {
                $this->command?->error('Failed to decompress cities data.');

                return;
            }

            $decompressedSize = strlen($decompressed);
            $this->command?->info('✓ Decompressed '.number_format($decompressedSize / 1024 / 1024, 2).' MB');
            $this->command?->newLine();

            $this->command?->info('📄 Processing cities data with streaming parser...');
            $this->command?->comment('   Using memory-efficient streaming to handle large datasets...');
            $this->command?->newLine();

            $this->seedCitiesStreaming($decompressed);

        } catch (\Exception $e) {
            $this->command?->error('Error: '.$e->getMessage());

            return;
        }
    }

    private function seedCitiesStreaming(string $jsonData): void
    {
        $countriesTable = config('worldable.tables.countries', 'world_countries');
        $statesTable = config('worldable.tables.states', 'world_states');
        $countriesInstalled = Schema::hasTable($countriesTable) && DB::table($countriesTable)->exists();
        $statesInstalled = Schema::hasTable($statesTable) && DB::table($statesTable)->exists();

        $countryMap = $countriesInstalled ? $this->buildCountryMap() : [];
        $stateMap = $statesInstalled ? $this->buildStateMap() : [];

        $citiesTable = config('worldable.tables.cities', 'world_cities');

        // First pass: count items for progress bar
        $this->command?->info('📊 Counting cities...');
        $totalCount = substr_count($jsonData, '"name"');
        $this->command?->info('   Found approximately '.number_format($totalCount).' cities');
        $this->command?->newLine();

        $bar = $this->command?->getOutput()->createProgressBar($totalCount);
        $bar->start();

        $inserted = 0;
        $orphanedCountry = 0;
        $orphanedState = 0;
        $batch = [];

        // Stream parse the JSON
        $buffer = '';
        $depth = 0;
        $inArray = false;
        $currentCity = '';
        $braceDepth = 0;

        for ($i = 0; $i < strlen($jsonData); $i++) {
            $char = $jsonData[$i];

            if ($char === '[' && $depth === 0) {
                $inArray = true;
                $depth++;

                continue;
            }

            if ($inArray) {
                if ($char === '{') {
                    if ($braceDepth === 0) {
                        $currentCity = '{';
                    } else {
                        $currentCity .= $char;
                    }
                    $braceDepth++;
                } elseif ($char === '}') {
                    $braceDepth--;
                    $currentCity .= $char;

                    if ($braceDepth === 0) {
                        // We have a complete city object
                        $city = json_decode($currentCity, true);

                        if (is_array($city) && ! empty($city['name'])) {
                            $countryId = null;
                            $stateId = null;

                            if (! empty($city['country_code'])) {
                                $countryId = $countryMap[$city['country_code']] ?? null;
                            }

                            if ($countryId && ! empty($city['state_code'])) {
                                $stateKey = $countryId.'_'.$city['state_code'];
                                $stateId = $stateMap[$stateKey] ?? null;
                            }

                            $latitude = $this->sanitizeCoordinate($city['latitude'] ?? null, -90, 90);
                            $longitude = $this->sanitizeCoordinate($city['longitude'] ?? null, -180, 180);

                            $batch[] = [
                                'country_id' => $countryId,
                                'state_id' => $stateId,
                                'name' => trim($city['name']),
                                'latitude' => $latitude,
                                'longitude' => $longitude,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            $inserted++;
                            if (! $countryId) {
                                $orphanedCountry++;
                            }
                            if (! $stateId && ! empty($city['state_code'])) {
                                $orphanedState++;
                            }

                            if (count($batch) >= self::CHUNK_SIZE) {
                                $this->insertBatch($citiesTable, $batch);
                                $batch = [];
                            }

                            $bar->advance();
                        }

                        $currentCity = '';
                    }
                } else {
                    if ($braceDepth > 0) {
                        $currentCity .= $char;
                    }
                }
            }
        }

        // Insert remaining batch
        if (! empty($batch)) {
            $this->insertBatch($citiesTable, $batch);
        }

        $bar->finish();
        $this->command?->newLine(2);

        $this->command?->info('✓ Seeded '.number_format($inserted).' cities successfully.');

        if ($orphanedCountry > 0) {
            $this->command?->warn('⚠ '.number_format($orphanedCountry).' cities without country links (countries not installed).');
        }

        if ($orphanedState > 0) {
            $this->command?->warn('⚠ '.number_format($orphanedState).' cities without state links (states not installed).');
        }

        if ($orphanedCountry > 0 || $orphanedState > 0) {
            $this->command?->info("ℹ Run 'php artisan world:link' after installing dependencies to establish relationships.");
        }

        // Free memory
        unset($jsonData);
    }

    private function buildCountryMap(): array
    {
        $countriesTable = config('worldable.tables.countries', 'world_countries');

        $this->command?->info('Building country lookup map...');

        return DB::table($countriesTable)
            ->pluck('id', 'iso_code')
            ->toArray();
    }

    private function buildStateMap(): array
    {
        $statesTable = config('worldable.tables.states', 'world_states');

        $this->command?->info('Building state lookup map...');

        $states = DB::table($statesTable)
            ->select('id', 'country_id', 'code')
            ->whereNotNull('code')
            ->get();

        $map = [];
        foreach ($states as $state) {
            $key = $state->country_id.'_'.$state->code;
            $map[$key] = $state->id;
        }

        return $map;
    }

    private function sanitizeCoordinate(?string $value, float $min, float $max): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $floatValue = (float) $value;

        if ($floatValue < $min || $floatValue > $max) {
            return null;
        }

        return $floatValue;
    }

    private function insertBatch(string $table, array $batch): void
    {
        try {
            DB::table($table)->insertOrIgnore($batch);
        } catch (\Exception $e) {
            $this->command?->error('Batch insert failed: '.$e->getMessage());
        }
    }
}
