<?php

namespace Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
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

        $this->command->info('📥 Downloading countries data from GitHub...');
        $this->command->newLine();

        $jsonUrl = 'https://raw.githubusercontent.com/ritechoice23/countries-states-cities-database/master/json/countries.json';
        $jsonContent = file_get_contents($jsonUrl);

        if ($jsonContent === false) {
            $this->command->error('Failed to download countries data');

            return;
        }

        $downloadSize = strlen($jsonContent);
        $this->command->info('✓ Downloaded '.number_format($downloadSize / 1024, 2).' KB');
        $this->command->newLine();

        // Count countries for progress bar
        $totalCount = substr_count($jsonContent, '"name"');
        $this->command->info('📊 Processing approximately '.number_format($totalCount).' countries...');
        $this->command->newLine();

        $continentsInstalled = DB::table('world_continents')->exists();
        $subregionsInstalled = DB::table('world_subregions')->exists();

        $progress = $this->command->getOutput()->createProgressBar($totalCount);
        $progress->start();

        $inserted = 0;
        $orphanedContinent = 0;
        $orphanedSubregion = 0;

        // Stream parse the JSON
        $currentCountry = '';
        $braceDepth = 0;
        $inArray = false;

        for ($i = 0; $i < strlen($jsonContent); $i++) {
            $char = $jsonContent[$i];

            if ($char === '[' && ! $inArray) {
                $inArray = true;

                continue;
            }

            if ($inArray) {
                if ($char === '{') {
                    if ($braceDepth === 0) {
                        $currentCountry = '{';
                    } else {
                        $currentCountry .= $char;
                    }
                    $braceDepth++;
                } elseif ($char === '}') {
                    $braceDepth--;
                    $currentCountry .= $char;

                    if ($braceDepth === 0) {
                        $country = json_decode($currentCountry, true);

                        if (is_array($country) && ! empty($country['name'])) {
                            $continentId = null;
                            $subregionId = null;

                            if ($continentsInstalled && ! empty($country['region'])) {
                                $continentId = DB::table('world_continents')
                                    ->where('name', $country['region'])
                                    ->value('id');
                            }

                            if ($subregionsInstalled && ! empty($country['subregion'])) {
                                $subregionId = DB::table('world_subregions')
                                    ->where('name', $country['subregion'])
                                    ->value('id');
                            }

                            $additionalData = [
                                'capital' => $country['capital'] ?? null,
                                'native' => $country['native'] ?? null,
                                'nationality' => $country['nationality'] ?? null,
                                'tld' => $country['tld'] ?? null,
                                'numeric_code' => $country['numeric_code'] ?? null,
                                'population' => $country['population'] ?? null,
                                'currency_name' => $country['currency_name'] ?? null,
                                'currency_symbol' => $country['currency_symbol'] ?? null,
                                'timezones' => $country['timezones'] ?? [],
                                'translations' => $country['translations'] ?? [],
                                'emoji' => $country['emoji'] ?? null,
                                'emojiU' => $country['emojiU'] ?? null,
                                'currency_code' => $country['currency'] ?? null,
                                'continent_name' => $country['region'] ?? null,
                                'subregion_name' => $country['subregion'] ?? null,
                            ];

                            DB::table('world_countries')->updateOrInsert(
                                ['iso_code' => $country['iso2']],
                                [
                                    'name' => $country['name'],
                                    'iso_code' => $country['iso2'],
                                    'iso_code_3' => $country['iso3'],
                                    'calling_code' => isset($country['phonecode']) ? '+'.$country['phonecode'] : null,
                                    'continent_id' => $continentId,
                                    'subregion_id' => $subregionId,
                                    'metadata' => json_encode($additionalData),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );

                            $inserted++;
                            if (! $continentId) {
                                $orphanedContinent++;
                            }
                            if (! $subregionId && ! empty($country['subregion'])) {
                                $orphanedSubregion++;
                            }

                            $progress->advance();
                        }

                        $currentCountry = '';
                    }
                } else {
                    if ($braceDepth > 0) {
                        $currentCountry .= $char;
                    }
                }
            }
        }

        unset($jsonContent);
        $progress->finish();
        $this->command->newLine(2);
        $this->command->info("✓ Successfully seeded {$inserted} countries");

        if ($orphanedContinent > 0) {
            $this->command->warn("⚠ {$orphanedContinent} countries without continent links (continents not installed)");
        }

        if ($orphanedSubregion > 0) {
            $this->command->warn("⚠ {$orphanedSubregion} countries without subregion links (subregions not installed)");
        }

        if ($orphanedContinent > 0 || $orphanedSubregion > 0) {
            $this->command->info("ℹ Run 'php artisan world:link' after installing dependencies to establish relationships.");
        }
    }
}
