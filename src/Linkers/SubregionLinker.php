<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Linkers;

use Illuminate\Support\Facades\DB;

class SubregionLinker extends AbstractLinker
{
    public function getComponentName(): string
    {
        return 'subregions';
    }

    protected function getTableName(): string
    {
        return $this->getTableConfig('subregions');
    }

    public function link(bool $isDryRun, bool $force): LinkerResult
    {
        $this->displayInfo('Processing subregions...');

        $subregionsTable = $this->getTableName();
        $continentsTable = $this->getTableConfig('continents');

        $query = DB::table($subregionsTable);

        if (! $force) {
            $query->whereNull('continent_id');
        }

        $orphaned = $query->get();

        if ($orphaned->isEmpty()) {
            $this->displayInfo('  No orphaned subregions found.');

            return new LinkerResult(
                component: $this->getComponentName(),
                linked: 0,
                notFound: 0,
                total: 0,
            );
        }

        $this->displayInfo("  Found {$orphaned->count()} orphaned subregions");

        $continentMap = $this->buildMap($continentsTable, 'name', 'id');

        $linked = 0;
        $notFound = 0;

        foreach ($orphaned as $subregion) {
            /** @var string|null $dataJson */
            $dataJson = $subregion->data;
            $metadata = $this->decodeMetadata($dataJson);
            $continentName = $metadata['continent_name'] ?? null;

            if ($continentName && isset($continentMap[$continentName])) {
                $this->updateRecord(
                    $subregionsTable,
                    $subregion->id,
                    ['continent_id' => $continentMap[$continentName]],
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
