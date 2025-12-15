<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits;

use Illuminate\Support\Facades\Schema;

trait ManagesForeignKeys
{
    protected static function bootManagesForeignKeys(): void
    {
        static::saving(function (self $model): void {
            /** @var array<string, array{table: string}> $definitions */
            $definitions = $model->getForeignKeyDefinitions();

            foreach ($definitions as $column => $definition) {
                $referencedTable = $definition['table'];

                if (! Schema::hasTable($referencedTable) && $model->$column !== null) {
                    $model->$column = null;
                }
            }
        });
    }

    /**
     * Get foreign key definitions for this model.
     *
     * @return array<string, array{table: string}>
     */
    protected function getForeignKeyDefinitions(): array
    {
        return [];
    }
}
