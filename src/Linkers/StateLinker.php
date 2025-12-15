<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Linkers;

use Illuminate\Support\Facades\DB;

class StateLinker extends AbstractLinker
{
    public function getComponentName(): string
    {
        return 'states';
    }

    protected function getTableName(): string
    {
        return $this->getTableConfig('states');
    }

    public function link(bool $isDryRun, bool $force): LinkerResult
    {
        $this->displayInfo('Processing states...');

        $statesTable = $this->getTableName();

        if (! $this->tableExists($statesTable)) {
            $this->displayInfo('  Table does not exist. Skipping.');

            return new LinkerResult(
                component: $this->getComponentName(),
                linked: 0,
                notFound: 0,
                total: 0,
            );
        }

        $countriesTable = $this->getTableConfig('countries');

        $query = DB::table($statesTable);

        if (! $force) {
            $query->whereNull('country_id');
        }

        $orphaned = $query->get();

        if ($orphaned->isEmpty()) {
            $this->displayInfo('  No orphaned states found.');

            return new LinkerResult(
                component: $this->getComponentName(),
                linked: 0,
                notFound: 0,
                total: 0,
            );
        }

        $this->displayInfo("  Found {$orphaned->count()} orphaned states");

        $countryMapByCode = $this->buildMap($countriesTable, 'iso_code', 'id');
        $countryMapByName = $this->buildMap($countriesTable, 'name', 'id');

        $linked = 0;
        $notFound = 0;

        foreach ($orphaned as $state) {
            /** @var string|null $metadataJson */
            $metadataJson = $state->metadata;
            $metadata = $this->decodeMetadata($metadataJson, 'metadata');
            $countryCode = $metadata['country_code'] ?? null;
            $countryName = $metadata['country_name'] ?? null;

            $countryId = null;

            if ($countryCode && isset($countryMapByCode[$countryCode])) {
                $countryId = $countryMapByCode[$countryCode];
            } elseif ($countryName && isset($countryMapByName[$countryName])) {
                $countryId = $countryMapByName[$countryName];
            }

            if ($countryId) {
                $this->updateRecord(
                    $statesTable,
                    $state->id,
                    ['country_id' => $countryId],
                    $isDryRun
                );
                $linked++;
            } else {
                $notFound++;
            }
        }

        if ($isDryRun) {
            $this->displayInfo("  Would link: {$linked}, Not found: {$notFound}");
        } else {
            $this->displayInfo("  ✓ Linked: {$linked}, Not found: {$notFound}");
        }

        return new LinkerResult(
            component: $this->getComponentName(),
            linked: $linked,
            notFound: $notFound,
            total: $orphaned->count(),
        );
    }
}
