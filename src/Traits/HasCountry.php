<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ritechoice23\Worldable\Models\Country;
use Ritechoice23\Worldable\Traits\Concerns\InteractsWithWorldEntities;

/**
 * HasCountry Trait
 *
 * Provides country functionality to models
 */
trait HasCountry
{
    use InteractsWithWorldEntities;

    public function countries(): MorphToMany
    {
        $this->ensureWorldablesTableExists();

        $countryMorphClass = (new Country)->getMorphClass();

        return $this->morphToMany(Country::class, 'worldable', 'worldables', 'worldable_id', 'world_entity_id')
            ->withPivot('group', 'world_entity_type', 'meta')
            ->wherePivot('world_entity_type', $countryMorphClass)
            ->withTimestamps();
    }

    public function attachCountry(Country|string|int $country, ?string $group = null, array $meta = []): self
    {
        return $this->attachEntity('countries', 'resolveCountry', $country, $group, $meta);
    }

    public function detachCountry(Country|string|int $country, ?string $group = null): self
    {
        return $this->detachEntity('countries', 'resolveCountry', $country, $group);
    }

    public function syncCountries(array $countries, ?string $group = null, array $meta = []): self
    {
        return $this->syncEntities('countries', 'resolveCountry', $countries, $group, $meta);
    }

    public function detachAllCountries(?string $group = null): self
    {
        return $this->detachAllEntities('countries', $group);
    }

    /**
     * Check if model has a specific country attached
     */
    public function hasCountry(Country|string|int $country, ?string $group = null): bool
    {
        return $this->hasEntity('countries', 'resolveCountry', $country, $group);
    }

    /**
     * Bulk attach countries
     */
    public function attachCountries(array $countries, ?string $group = null, array $meta = []): self
    {
        foreach ($countries as $country) {
            $this->attachCountry($country, $group, $meta);
        }

        return $this;
    }

    /**
     * Fluent method to set location at once
     * Usage: $user->locateAt(['country' => 'Nigeria', 'state' => 'Lagos', 'city' => 'Ikeja'])
     */
    public function locateAt(array $location, ?string $group = null): self
    {
        if (isset($location['country'])) {
            $this->attachCountry($location['country'], $group);
        }

        if (isset($location['state']) && method_exists($this, 'attachState')) {
            $this->attachState($location['state'], $group);
        }

        if (isset($location['city']) && method_exists($this, 'attachCity')) {
            $this->attachCity($location['city'], $group);
        }

        return $this;
    }

    // Accessors
    public function getCountryNameAttribute(): ?string
    {
        if ($this->relationLoaded('countries')) {
            return $this->countries->first()?->name;
        }

        return $this->countries()->first()?->name;
    }

    public function getCountryCodeAttribute(): ?string
    {
        if ($this->relationLoaded('countries')) {
            return $this->countries->first()?->iso_code;
        }

        return $this->countries()->first()?->iso_code;
    }

    public function getCountryCallingCodeAttribute(): ?string
    {
        if ($this->relationLoaded('countries')) {
            return $this->countries->first()?->calling_code;
        }

        return $this->countries()->first()?->calling_code;
    }

    // Scopes
    public function scopeWhereFrom(Builder $query, Country|string|int $country, ?string $group = null): Builder
    {
        return $this->scopeWhereHasEntity($query, 'countries', 'resolveCountry', $country, $group);
    }

    public function scopeWhereNotFrom(Builder $query, Country|string|int $country, ?string $group = null): Builder
    {
        return $this->scopeWhereDoesntHaveEntity($query, 'countries', 'resolveCountry', $country, $group);
    }

    public function scopeWhereHasCountry(Builder $query, Country|string|int $country, ?string $group = null): Builder
    {
        return $this->scopeWhereFrom($query, $country, $group);
    }

    public function scopeFromCountry(Builder $query, Country|string|int $country, ?string $group = null): Builder
    {
        return $this->scopeWhereFrom($query, $country, $group);
    }

    public function scopeWhereInCountries(Builder $query, array $countries, ?string $group = null): Builder
    {
        $countryIds = collect($countries)
            ->map(fn ($country) => $this->resolveCountry($country)?->id)
            ->filter()
            ->toArray();

        return $query->whereHas('countries', function ($q) use ($countryIds, $group) {
            $q->whereIn('world_entity_id', $countryIds);

            if ($group !== null) {
                $q->wherePivot('group', $group);
            }
        });
    }

    protected function resolveCountry(Country|string|int $value): ?Country
    {
        if ($value instanceof Country) {
            return $value;
        }

        if (is_numeric($value)) {
            return Country::find($value);
        }

        if (is_string($value)) {
            return Country::where('name', $value)
                ->orWhere('iso_code', strtoupper($value))
                ->orWhere('iso_code_3', strtoupper($value))
                ->first();
        }

        return null;
    }

    /**
     * Get meta for a specific country
     */
    public function getCountryMeta(
        Country|string|int $country,
        ?string $key = null,
        mixed $default = null,
        ?string $group = null
    ): mixed {
        $countryModel = $this->resolveCountry($country);

        if (! $countryModel) {
            return $default;
        }

        $query = $this->countries()->where('world_entity_id', $countryModel->id);

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
     * Update meta for a specific country
     */
    public function updateCountryMeta(
        Country|string|int $country,
        array $meta,
        ?string $group = null,
        bool $merge = true
    ): self {
        $countryModel = $this->resolveCountry($country);

        if (! $countryModel) {
            return $this;
        }

        $query = $this->countries()->where('world_entity_id', $countryModel->id);

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
     * Check if country has specific meta key
     */
    public function hasCountryMeta(
        Country|string|int $country,
        string $key,
        ?string $group = null
    ): bool {
        $countryModel = $this->resolveCountry($country);

        if (! $countryModel) {
            return false;
        }

        $query = $this->countries()->where('world_entity_id', $countryModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        return $pivot?->hasMeta($key) ?? false;
    }
}
