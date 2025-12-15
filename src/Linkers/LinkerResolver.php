<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Linkers;

use Illuminate\Support\Str;

class LinkerResolver
{
    private const LINKER_NAMESPACE = 'Ritechoice23\\Worldable\\Linkers\\';

    /**
     * @var array<string, AbstractLinker>
     */
    private array $linkers = [];

    public function resolve(string $component): ?AbstractLinker
    {
        if (isset($this->linkers[$component])) {
            return $this->linkers[$component];
        }

        $linkerClass = $this->resolveLinkerClass($component);

        if (! $linkerClass || ! class_exists($linkerClass)) {
            return null;
        }

        $linker = app($linkerClass);

        if (! $linker instanceof AbstractLinker) {
            return null;
        }

        $this->linkers[$component] = $linker;

        return $linker;
    }

    /**
     * @return array<string, AbstractLinker>
     */
    public function resolveAll(): array
    {
        $components = ['subregions', 'countries', 'states', 'cities'];
        /** @var array<string, AbstractLinker> $linkers */
        $linkers = [];

        foreach ($components as $component) {
            $linker = $this->resolve($component);
            if ($linker) {
                $linkers[$component] = $linker;
            }
        }

        return $linkers;
    }

    public function has(string $component): bool
    {
        return $this->resolve($component) !== null;
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableComponents(): array
    {
        return array_keys($this->resolveAll());
    }

    private function resolveLinkerClass(string $component): string
    {
        // Convert component name to StudlyCase and append 'Linker'
        // Examples:
        //   'subregions' -> 'SubregionLinker'
        //   'countries' -> 'CountryLinker'
        //   'states' -> 'StateLinker'
        //   'cities' -> 'CityLinker'

        $singularComponent = Str::singular($component);
        $studlyName = Str::studly($singularComponent);

        return self::LINKER_NAMESPACE.$studlyName.'Linker';
    }
}
