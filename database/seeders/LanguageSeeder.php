<?php

namespace Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LanguageSeeder extends Seeder
{
    protected $command;

    /**
     * The URL to fetch language data from.
     */
    protected string $languageDataUrl = 'https://raw.githubusercontent.com/ritechoice23/world/master/resources/json/languages.json';

    /**
     * Set the command instance.
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Seed world languages from remote JSON source.
     */
    public function run(): void
    {
        $this->command->info('Fetching language data from remote source...');

        try {
            $response = Http::timeout(30)->get($this->languageDataUrl);

            if (! $response->successful()) {
                $this->command->error('Failed to fetch language data. HTTP Status: '.$response->status());

                return;
            }

            $languagesData = $response->json();

            if (empty($languagesData)) {
                $this->command->error('No language data found in the response.');

                return;
            }

            $count = 0;

            foreach ($languagesData as $language) {
                DB::table('world_languages')->updateOrInsert(
                    ['iso_code' => $language['code']],
                    [
                        'name' => $language['name'],
                        'iso_code' => $language['code'],
                        'native_name' => $language['name_native'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $count++;
            }

            $this->command->info("Seeded {$count} languages successfully.");
        } catch (\Exception $e) {
            $this->command->error('Error fetching or processing language data: '.$e->getMessage());
        }
    }
}
