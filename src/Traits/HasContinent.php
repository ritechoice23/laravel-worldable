<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ritechoice23\Worldable\Models\Continent;
use Ritechoice23\Worldable\Traits\Concerns\InteractsWithWorldEntities;

/**
 * HasContinent Trait
 *
 * Provides continent functionality to models
 */
trait HasContinent
{
    use InteractsWithWorldEntities;

    public function continents(): MorphToMany
    {
        $this->ensureWorldablesTableExists();

        $continentMorphClass = (new Continent)->getMorphClass();

        return $this->morphToMany(Continent::class, 'worldable', 'worldables', 'worldable_id', 'world_entity_id')
            ->withPivot('group', 'world_entity_type', 'meta')
            ->wherePivot('world_entity_type', $continentMorphClass)
            ->withTimestamps();
    }

    public function attachContinent(Continent|string|int $continent, ?string $group = null, array $meta = []): self
    {
        return $this->attachEntity('continents', 'resolveContinent', $continent, $group, $meta);
    }

    public function detachContinent(Continent|string|int $continent, ?string $group = null): self
    {
        return $this->detachEntity('continents', 'resolveContinent', $continent, $group);
    }

    public function syncContinents(array $continents, ?string $group = null, array $meta = []): self
    {
        return $this->syncEntities('continents', 'resolveContinent', $continents, $group, $meta);
    }

    public function detachAllContinents(?string $group = null): self
    {
        return $this->detachAllEntities('continents', $group);
    }

    public function hasContinent(Continent|string|int $continent, ?string $group = null): bool
    {
        return $this->hasEntity('continents', 'resolveContinent', $continent, $group);
    }

    public function attachContinents(array $continents, ?string $group = null, array $meta = []): self
    {
        foreach ($continents as $continent) {
            $this->attachContinent($continent, $group, $meta);
        }

        return $this;
    }

    // Accessors
    public function getContinentNameAttribute(): ?string
    {
        if ($this->relationLoaded('continents')) {
            return $this->continents->first()?->name;
        }

        return $this->continents()->first()?->name;
    }

    public function getContinentCodeAttribute(): ?string
    {
        if ($this->relationLoaded('continents')) {
            return $this->continents->first()?->code;
        }

        return $this->continents()->first()?->code;
    }

    // Scopes
    public function scopeWhereInContinent(Builder $query, Continent|string|int $continent, ?string $group = null): Builder
    {
        return $this->scopeWhereHasEntity($query, 'continents', 'resolveContinent', $continent, $group);
    }

    public function scopeWhereNotInContinent(Builder $query, Continent|string|int $continent, ?string $group = null): Builder
    {
        return $this->scopeWhereDoesntHaveEntity($query, 'continents', 'resolveContinent', $continent, $group);
    }

    protected function resolveContinent(Continent|string|int $value): ?Continent
    {
        if ($value instanceof Continent) {
            return $value;
        }

        if (is_numeric($value)) {
            return Continent::find($value);
        }

        if (is_string($value)) {
            return Continent::where('name', $value)
                ->orWhere('code', strtoupper($value))
                ->first();
        }

        return null;
    }

    /**
     * Get meta for a specific continent
     */
    public function getContinentMeta(
        Continent|string|int $continent,
        ?string $key = null,
        mixed $default = null,
        ?string $group = null
    ): mixed {
        $continentModel = $this->resolveContinent($continent);

        if (! $continentModel) {
            return $default;
        }

        $query = $this->continents()->where('world_entity_id', $continentModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        if (! $pivot) {
            return $default;
        }

        return $pivot->getMeta($key, $default);
    }

    /**
     * Update meta for a specific continent
     */
    public function updateContinentMeta(
        Continent|string|int $continent,
        array $meta,
        ?string $group = null,
        bool $merge = true
    ): self {
        $continentModel = $this->resolveContinent($continent);

        if (! $continentModel) {
            return $this;
        }

        $query = $this->continents()->where('world_entity_id', $continentModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        if ($pivot) {
            if ($merge) {
                $pivot->mergeMeta($meta);
            } else {
                $pivot->setMeta($meta);
            }
            $pivot->save();
        }

        return $this;
    }

    /**
     * Check if continent has specific meta key
     */
    public function hasContinentMeta(
        Continent|string|int $continent,
        string $key,
        ?string $group = null
    ): bool {
        $continentModel = $this->resolveContinent($continent);

        if (! $continentModel) {
            return false;
        }

        $query = $this->continents()->where('world_entity_id', $continentModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        return $pivot?->hasMeta($key) ?? false;
    }
}
