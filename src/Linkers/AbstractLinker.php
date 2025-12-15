<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Linkers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractLinker
{
    protected ?SymfonyStyle $output = null;

    public function setOutput(SymfonyStyle $output): self
    {
        $this->output = $output;

        return $this;
    }

    abstract public function link(bool $isDryRun, bool $force): LinkerResult;

    abstract public function getComponentName(): string;

    abstract protected function getTableName(): string;

    protected function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    protected function displayInfo(string $message): void
    {
        if ($this->output) {
            $this->output->writeln($message);
        }
    }

    /**
     * Build a map of key-value pairs from a database table.
     *
     * @return array<string|int, mixed>
     */
    protected function buildMap(string $table, string $keyColumn, string $valueColumn): array
    {
        return DB::table($table)
            ->pluck($valueColumn, $keyColumn)
            ->toArray();
    }

    /**
     * Build a map with composite keys from a database table.
     *
     * @param  array<int, string>  $selectColumns
     * @param  array<int, string>  $keyColumns
     * @return array<string, mixed>
     */
    protected function buildMultiKeyMap(string $table, array $selectColumns, array $keyColumns): array
    {
        $records = DB::table($table)
            ->select($selectColumns)
            ->get();

        $map = [];
        foreach ($records as $record) {
            $key = implode('_', array_map(fn (string $col): mixed => $record->$col, $keyColumns));
            $map[$key] = $record->id;
        }

        return $map;
    }

    protected function getTableConfig(string $component): string
    {
        return (string) config("worldable.tables.{$component}", "world_{$component}");
    }

    /**
     * Decode JSON metadata.
     *
     * @return array<string, mixed>
     */
    protected function decodeMetadata(?string $json, string $key = 'data'): array
    {
        if (! $json) {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function updateRecord(string $table, int $id, array $updates, bool $isDryRun): bool
    {
        if ($isDryRun) {
            return true;
        }

        DB::table($table)
            ->where('id', $id)
            ->update($updates);

        return true;
    }
}
