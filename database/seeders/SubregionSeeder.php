<?php

namespace Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubregionSeeder extends Seeder
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
        $subregions = [
            // Africa
            ['name' => 'Northern Africa', 'code' => 'NAF', 'continent' => 'Africa'],
            ['name' => 'Western Africa', 'code' => 'WAF', 'continent' => 'Africa'],
            ['name' => 'Middle Africa', 'code' => 'MAF', 'continent' => 'Africa'],
            ['name' => 'Eastern Africa', 'code' => 'EAF', 'continent' => 'Africa'],
            ['name' => 'Southern Africa', 'code' => 'SAF', 'continent' => 'Africa'],

            // Asia
            ['name' => 'Central Asia', 'code' => 'CAS', 'continent' => 'Asia'],
            ['name' => 'Eastern Asia', 'code' => 'EAS', 'continent' => 'Asia'],
            ['name' => 'South-eastern Asia', 'code' => 'SEA', 'continent' => 'Asia'],
            ['name' => 'Southern Asia', 'code' => 'SAS', 'continent' => 'Asia'],
            ['name' => 'Western Asia', 'code' => 'WAS', 'continent' => 'Asia'],

            // Europe
            ['name' => 'Eastern Europe', 'code' => 'EEU', 'continent' => 'Europe'],
            ['name' => 'Northern Europe', 'code' => 'NEU', 'continent' => 'Europe'],
            ['name' => 'Southern Europe', 'code' => 'SEU', 'continent' => 'Europe'],
            ['name' => 'Western Europe', 'code' => 'WEU', 'continent' => 'Europe'],

            // North America
            ['name' => 'Caribbean', 'code' => 'CAR', 'continent' => 'North America'],
            ['name' => 'Central America', 'code' => 'CAM', 'continent' => 'North America'],
            ['name' => 'Northern America', 'code' => 'NAM', 'continent' => 'North America'],

            // South America
            ['name' => 'South America', 'code' => 'SAM', 'continent' => 'South America'],

            // Oceania
            ['name' => 'Australia and New Zealand', 'code' => 'ANZ', 'continent' => 'Oceania'],
            ['name' => 'Melanesia', 'code' => 'MEL', 'continent' => 'Oceania'],
            ['name' => 'Micronesia', 'code' => 'MIC', 'continent' => 'Oceania'],
            ['name' => 'Polynesia', 'code' => 'POL', 'continent' => 'Oceania'],

            // Antarctica
            ['name' => 'Antarctica', 'code' => 'ANT', 'continent' => 'Antarctica'],
        ];

        $continentsInstalled = DB::table('world_continents')->exists();
        $inserted = 0;
        $orphaned = 0;

        foreach ($subregions as $subregion) {
            $continentId = null;

            if ($continentsInstalled) {
                $continentId = DB::table('world_continents')
                    ->where('name', $subregion['continent'])
                    ->value('id');
            }

            DB::table('world_subregions')->updateOrInsert(
                ['name' => $subregion['name']],
                [
                    'name' => $subregion['name'],
                    'code' => $subregion['code'],
                    'continent_id' => $continentId,
                    'data' => json_encode([
                        'continent_name' => $subregion['continent'],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            if ($continentId) {
                $inserted++;
            } else {
                $orphaned++;
            }
        }

        $this->command->info("✓ Seeded {$inserted} subregions successfully.");

        if ($orphaned > 0) {
            $this->command->warn("⚠ {$orphaned} subregions created without continent links (continents not installed).");
            $this->command->info("ℹ Run 'php artisan world:link' after installing continents to establish relationships.");
        }
    }
}
