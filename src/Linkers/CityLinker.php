<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Linkers;

use Illuminate\Support\Facades\DB;

class CityLinker extends AbstractLinker
{
    private const BATCH_SIZE = 500;

    public function getComponentName(): string
    {
        return 'cities';
    }

    protected function getTableName(): string
    {
        return $this->getTableConfig('cities');
    }

    public function link(bool $isDryRun, bool $force): LinkerResult
    {
        $this->displayInfo('Processing cities...');

        $citiesTable = $this->getTableName();

        if (! $this->tableExists($citiesTable)) {
            $this->displayInfo('  Table does not exist. Skipping.');

            return new LinkerResult(
                component: $this->getComponentName(),
                linked: 0,
                notFound: 0,
                total: 0,
            );
        }

        $countriesTable = $this->getTableConfig('countries');
        $statesTable = $this->getTableConfig('states');

        $this->displayInfo('  Building lookup maps...');

        $countryMap = $this->buildMap($countriesTable, 'iso_code', 'id');
        $stateMap = $this->buildMultiKeyMap(
            $statesTable,
            ['id', 'country_id', 'code'],
            ['country_id', 'code']
        );

        $query = DB::table($citiesTable);

        if (! $force) {
            $query->where(function ($q) {
                $q->whereNull('country_id')
                    ->orWhereNull('state_id');
            });
        }

        $total = $query->count();

        if ($total === 0) {
            $this->displayInfo('  No orphaned cities found.');

            return new LinkerResult(
                component: $this->getComponentName(),
                linked: 0,
                notFound: 0,
                total: 0,
            );
        }

        $this->displayInfo("  Found {$total} cities to process");

        $linkedCountry = 0;
        $linkedState = 0;

        $query->orderBy('id')->chunk(self::BATCH_SIZE, function ($cities) {
            // Cities don't store metadata with parent info in current implementation
            // This is mainly a placeholder for future enhancements
            // Real linking happens during seeding when external data is available
        });

        $this->displayInfo('  Note: Cities require country_code/state_code from source data.');
        $this->displayInfo('  Consider re-running the CitySeeder if countries/states were added after cities.');

        return new LinkerResult(
            component: $this->getComponentName(),
            linked: 0,
            notFound: 0,
            total: $total,
            details: [
                'countries' => 0,
                'states' => 0,
            ],
        );
    }
}
