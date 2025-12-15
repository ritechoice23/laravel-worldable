<?php

namespace Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CurrencySeeder extends Seeder
{
    protected $command;

    /**
     * The URL to fetch currency data from.
     */
    protected string $currencyDataUrl = 'https://raw.githubusercontent.com/ritechoice23/world/master/resources/json/currencies.json';

    /**
     * Set the command instance.
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Seed world currencies from remote JSON source.
     */
    public function run(): void
    {
        $this->command->info('Fetching currency data from remote source...');

        try {
            $response = Http::timeout(30)->get($this->currencyDataUrl);

            if (! $response->successful()) {
                $this->command->error('Failed to fetch currency data. HTTP Status: '.$response->status());

                return;
            }

            $currenciesData = $response->json();

            if (empty($currenciesData)) {
                $this->command->error('No currency data found in the response.');

                return;
            }

            $count = 0;

            foreach ($currenciesData as $code => $currency) {
                DB::table('world_currencies')->updateOrInsert(
                    ['code' => $code],
                    [
                        'name' => $currency['name'] ?? $code,
                        'code' => $code,
                        'symbol' => $currency['symbol_native'] ?? $currency['symbol'] ?? '',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $count++;
            }

            $this->command->info("Seeded {$count} currencies successfully.");
        } catch (\Exception $e) {
            $this->command->error('Error fetching or processing currency data: '.$e->getMessage());
        }
    }
}
