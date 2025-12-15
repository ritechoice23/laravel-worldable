<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ritechoice23\Worldable\Models\Subregion;
use Ritechoice23\Worldable\Traits\Concerns\InteractsWithWorldEntities;

trait HasSubregion
{
    use InteractsWithWorldEntities;

    public function subregions(): MorphToMany
    {
        $this->ensureWorldablesTableExists();

        $subregionMorphClass = (new Subregion)->getMorphClass();

        return $this->morphToMany(Subregion::class, 'worldable', 'worldables', 'worldable_id', 'world_entity_id')
            ->withPivot('group', 'world_entity_type', 'meta')
            ->wherePivot('world_entity_type', $subregionMorphClass)
            ->withTimestamps();
    }

    public function attachSubregion(Subregion|string|int $subregion, ?string $group = null, array $meta = []): self
    {
        return $this->attachEntity('subregions', 'resolveSubregion', $subregion, $group, $meta);
    }

    public function detachSubregion(Subregion|string|int $subregion, ?string $group = null): self
    {
        return $this->detachEntity('subregions', 'resolveSubregion', $subregion, $group);
    }

    public function syncSubregions(array $subregions, ?string $group = null, array $meta = []): self
    {
        return $this->syncEntities('subregions', 'resolveSubregion', $subregions, $group, $meta);
    }

    public function detachAllSubregions(?string $group = null): self
    {
        return $this->detachAllEntities('subregions', $group);
    }

    public function hasSubregion(Subregion|string|int $subregion, ?string $group = null): bool
    {
        return $this->hasEntity('subregions', 'resolveSubregion', $subregion, $group);
    }

    public function attachSubregions(array $subregions, ?string $group = null, array $meta = []): self
    {
        foreach ($subregions as $subregion) {
            $this->attachSubregion($subregion, $group, $meta);
        }

        return $this;
    }

    // Accessors
    public function getSubregionNameAttribute(): ?string
    {
        if ($this->relationLoaded('subregions')) {
            return $this->subregions->first()?->name;
        }

        return $this->subregions()->first()?->name;
    }

    public function getSubregionCodeAttribute(): ?string
    {
        if ($this->relationLoaded('subregions')) {
            return $this->subregions->first()?->code;
        }

        return $this->subregions()->first()?->code;
    }

    // Scopes
    public function scopeWhereInSubregion(Builder $query, Subregion|string|int $subregion, ?string $group = null): Builder
    {
        return $this->scopeWhereHasEntity($query, 'subregions', 'resolveSubregion', $subregion, $group);
    }

    public function scopeWhereNotInSubregion(Builder $query, Subregion|string|int $subregion, ?string $group = null): Builder
    {
        return $this->scopeWhereDoesntHaveEntity($query, 'subregions', 'resolveSubregion', $subregion, $group);
    }

    protected function resolveSubregion(Subregion|string|int $value): ?Subregion
    {
        if ($value instanceof Subregion) {
            return $value;
        }

        if (is_string($value)) {
            $subregion = Subregion::where('code', $value)->first();
            if ($subregion) {
                return $subregion;
            }

            return Subregion::where('name', $value)->first();
        }

        if (is_numeric($value)) {
            return Subregion::find($value);
        }

        return null;
    }

    /**
     * Get meta for a specific subregion
     */
    public function getSubregionMeta(
        Subregion|string|int $subregion,
        ?string $key = null,
        mixed $default = null,
        ?string $group = null
    ): mixed {
        $subregionModel = $this->resolveSubregion($subregion);

        if (! $subregionModel) {
            return $default;
        }

        $query = $this->subregions()->where('world_entity_id', $subregionModel->id);

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
     * Update meta for a specific subregion
     */
    public function updateSubregionMeta(
        Subregion|string|int $subregion,
        array $meta,
        ?string $group = null,
        bool $merge = true
    ): self {
        $subregionModel = $this->resolveSubregion($subregion);

        if (! $subregionModel) {
            return $this;
        }

        $query = $this->subregions()->where('world_entity_id', $subregionModel->id);

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
     * Check if subregion has specific meta key
     */
    public function hasSubregionMeta(
        Subregion|string|int $subregion,
        string $key,
        ?string $group = null
    ): bool {
        $subregionModel = $this->resolveSubregion($subregion);

        if (! $subregionModel) {
            return false;
        }

        $query = $this->subregions()->where('world_entity_id', $subregionModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        return $pivot?->hasMeta($key) ?? false;
    }
}
