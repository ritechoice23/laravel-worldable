<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Linkers;

use Illuminate\Support\Facades\DB;

class CountryLinker extends AbstractLinker
{
    public function getComponentName(): string
    {
        return 'countries';
    }

    protected function getTableName(): string
    {
        return $this->getTableConfig('countries');
    }

    public function link(bool $isDryRun, bool $force): LinkerResult
    {
        $this->displayInfo('Processing countries...');

        $countriesTable = $this->getTableName();
        $continentsTable = $this->getTableConfig('continents');
        $subregionsTable = $this->getTableConfig('subregions');

        $query = DB::table($countriesTable);

        if (! $force) {
            $query->where(function (\Illuminate\Database\Query\Builder $q): void {
                $q->whereNull('continent_id')
                    ->orWhereNull('subregion_id');
            });
        }

        $orphaned = $query->get();

        if ($orphaned->isEmpty()) {
            $this->displayInfo('  No orphaned countries found.');

            return new LinkerResult(
                component: $this->getComponentName(),
                linked: 0,
                notFound: 0,
                total: 0,
            );
        }

        $this->displayInfo("  Found {$orphaned->count()} countries to process");

        $continentMap = $this->buildMap($continentsTable, 'name', 'id');
        $subregionMap = $this->buildMap($subregionsTable, 'name', 'id');

        $linkedContinent = 0;
        $linkedSubregion = 0;

        foreach ($orphaned as $country) {
            $metadata = $this->decodeMetadata($country->metadata, 'metadata');
            $updates = [];

            if ((! $country->continent_id || $force) && ! empty($metadata['continent_name'])) {
                $continentName = $metadata['continent_name'];
                if (isset($continentMap[$continentName])) {
                    $updates['continent_id'] = $continentMap[$continentName];
                    $linkedContinent++;
                }
            }

            if ((! $country->subregion_id || $force) && ! empty($metadata['subregion_name'])) {
                $subregionName = $metadata['subregion_name'];
                if (isset($subregionMap[$subregionName])) {
                    $updates['subregion_id'] = $subregionMap[$subregionName];
                    $linkedSubregion++;
                }
            }

            if (! empty($updates)) {
                $this->updateRecord($countriesTable, $country->id, $updates, $isDryRun);
            }
        }

        if ($isDryRun) {
            $this->displayInfo("  Would link continents: {$linkedContinent}, subregions: {$linkedSubregion}");
        } else {
            $this->displayInfo("  ✓ Linked continents: {$linkedContinent}, subregions: {$linkedSubregion}");
        }

        return new LinkerResult(
            component: $this->getComponentName(),
            linked: $linkedContinent + $linkedSubregion,
            notFound: 0,
            total: $orphaned->count(),
            details: [
                'continents' => $linkedContinent,
                'subregions' => $linkedSubregion,
            ],
        );
    }
}
