<?php

namespace Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContinentSeeder extends Seeder
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

    /**
     * Seed the continents table with all 7 continents.
     */
    public function run(): void
    {
        $continents = [
            ['name' => 'Africa', 'code' => 'AF'],
            ['name' => 'Antarctica', 'code' => 'AN'],
            ['name' => 'Asia', 'code' => 'AS'],
            ['name' => 'Europe', 'code' => 'EU'],
            ['name' => 'North America', 'code' => 'NA'],
            ['name' => 'Oceania', 'code' => 'OC'],
            ['name' => 'South America', 'code' => 'SA'],
        ];

        foreach ($continents as $continent) {
            DB::table('world_continents')->updateOrInsert(
                ['code' => $continent['code']],
                array_merge($continent, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Seeded 7 continents successfully.');
    }
}
