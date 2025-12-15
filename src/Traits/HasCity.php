<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ritechoice23\Worldable\Models\City;
use Ritechoice23\Worldable\Traits\Concerns\InteractsWithWorldEntities;

trait HasCity
{
    use InteractsWithWorldEntities;

    public function cities(): MorphToMany
    {
        $this->ensureWorldablesTableExists();

        $cityMorphClass = (new City)->getMorphClass();

        return $this->morphToMany(City::class, 'worldable', 'worldables', 'worldable_id', 'world_entity_id')
            ->withPivot('group', 'world_entity_type', 'meta')
            ->wherePivot('world_entity_type', $cityMorphClass)
            ->withTimestamps();
    }

    public function attachCity(City|string|int $city, ?string $group = null, array $meta = []): self
    {
        return $this->attachEntity('cities', 'resolveCity', $city, $group, $meta);
    }

    public function detachCity(City|string|int $city, ?string $group = null): self
    {
        return $this->detachEntity('cities', 'resolveCity', $city, $group);
    }

    public function syncCities(array $cities, ?string $group = null, array $meta = []): self
    {
        return $this->syncEntities('cities', 'resolveCity', $cities, $group, $meta);
    }

    public function detachAllCities(?string $group = null): self
    {
        return $this->detachAllEntities('cities', $group);
    }

    public function hasCity(City|string|int $city, ?string $group = null): bool
    {
        return $this->hasEntity('cities', 'resolveCity', $city, $group);
    }

    public function attachCities(array $cities, ?string $group = null, array $meta = []): self
    {
        foreach ($cities as $city) {
            $this->attachCity($city, $group, $meta);
        }

        return $this;
    }

    // Accessors
    public function getCityNameAttribute(): ?string
    {
        if ($this->relationLoaded('cities')) {
            return $this->cities->first()?->name;
        }

        return $this->cities()->first()?->name;
    }

    public function getCityCoordinatesAttribute(): ?array
    {
        $city = $this->relationLoaded('cities')
            ? $this->cities->first()
            : $this->cities()->first();

        if ($city && $city->latitude && $city->longitude) {
            return [
                'lat' => $city->latitude,
                'lng' => $city->longitude,
            ];
        }

        return null;
    }

    // Scopes
    public function scopeWhereInCity(Builder $query, City|string|int $city, ?string $group = null): Builder
    {
        return $this->scopeWhereHasEntity($query, 'cities', 'resolveCity', $city, $group);
    }

    public function scopeWhereNotInCity(Builder $query, City|string|int $city, ?string $group = null): Builder
    {
        return $this->scopeWhereDoesntHaveEntity($query, 'cities', 'resolveCity', $city, $group);
    }

    protected function resolveCity(City|string|int $value): ?City
    {
        if ($value instanceof City) {
            return $value;
        }

        if (is_numeric($value)) {
            return City::find($value);
        }

        if (is_string($value)) {
            return City::where('name', $value)->first();
        }

        return null;
    }

    /**
     * Get meta for a specific city
     */
    public function getCityMeta(
        City|string|int $city,
        ?string $key = null,
        mixed $default = null,
        ?string $group = null
    ): mixed {
        $cityModel = $this->resolveCity($city);

        if (! $cityModel) {
            return $default;
        }

        $query = $this->cities()->where('world_entity_id', $cityModel->id);

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
     * Update meta for a specific city
     */
    public function updateCityMeta(
        City|string|int $city,
        array $meta,
        ?string $group = null,
        bool $merge = true
    ): self {
        $cityModel = $this->resolveCity($city);

        if (! $cityModel) {
            return $this;
        }

        $query = $this->cities()->where('world_entity_id', $cityModel->id);

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
     * Check if city has specific meta key
     */
    public function hasCityMeta(
        City|string|int $city,
        string $key,
        ?string $group = null
    ): bool {
        $cityModel = $this->resolveCity($city);

        if (! $cityModel) {
            return false;
        }

        $query = $this->cities()->where('world_entity_id', $cityModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        return $pivot?->hasMeta($key) ?? false;
    }
}
