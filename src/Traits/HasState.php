<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ritechoice23\Worldable\Models\State;
use Ritechoice23\Worldable\Traits\Concerns\InteractsWithWorldEntities;

trait HasState
{
    use InteractsWithWorldEntities;

    public function states(): MorphToMany
    {
        $this->ensureWorldablesTableExists();

        $stateMorphClass = (new State)->getMorphClass();

        return $this->morphToMany(State::class, 'worldable', 'worldables', 'worldable_id', 'world_entity_id')
            ->withPivot('group', 'world_entity_type', 'meta')
            ->wherePivot('world_entity_type', $stateMorphClass)
            ->withTimestamps();
    }

    public function attachState(State|string|int $state, ?string $group = null, array $meta = []): self
    {
        return $this->attachEntity('states', 'resolveState', $state, $group, $meta);
    }

    public function detachState(State|string|int $state, ?string $group = null): self
    {
        return $this->detachEntity('states', 'resolveState', $state, $group);
    }

    public function syncStates(array $states, ?string $group = null, array $meta = []): self
    {
        return $this->syncEntities('states', 'resolveState', $states, $group, $meta);
    }

    public function detachAllStates(?string $group = null): self
    {
        return $this->detachAllEntities('states', $group);
    }

    public function hasState(State|string|int $state, ?string $group = null): bool
    {
        return $this->hasEntity('states', 'resolveState', $state, $group);
    }

    public function attachStates(array $states, ?string $group = null, array $meta = []): self
    {
        foreach ($states as $state) {
            $this->attachState($state, $group, $meta);
        }

        return $this;
    }

    // Accessors
    public function getStateNameAttribute(): ?string
    {
        if ($this->relationLoaded('states')) {
            return $this->states->first()?->name;
        }

        return $this->states()->first()?->name;
    }

    public function getStateCodeAttribute(): ?string
    {
        if ($this->relationLoaded('states')) {
            return $this->states->first()?->code;
        }

        return $this->states()->first()?->code;
    }

    // Scopes
    public function scopeWhereInState(Builder $query, State|string|int $state, ?string $group = null): Builder
    {
        return $this->scopeWhereHasEntity($query, 'states', 'resolveState', $state, $group);
    }

    public function scopeWhereNotInState(Builder $query, State|string|int $state, ?string $group = null): Builder
    {
        return $this->scopeWhereDoesntHaveEntity($query, 'states', 'resolveState', $state, $group);
    }

    protected function resolveState(State|string|int $value): ?State
    {
        if ($value instanceof State) {
            return $value;
        }

        if (is_numeric($value)) {
            return State::find($value);
        }

        if (is_string($value)) {
            return State::where('name', $value)
                ->orWhere('code', strtoupper($value))
                ->first();
        }

        return null;
    }

    /**
     * Get meta for a specific state
     */
    public function getStateMeta(
        State|string|int $state,
        ?string $key = null,
        mixed $default = null,
        ?string $group = null
    ): mixed {
        $stateModel = $this->resolveState($state);

        if (! $stateModel) {
            return $default;
        }

        $query = $this->states()->where('world_entity_id', $stateModel->id);

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
     * Update meta for a specific state
     */
    public function updateStateMeta(
        State|string|int $state,
        array $meta,
        ?string $group = null,
        bool $merge = true
    ): self {
        $stateModel = $this->resolveState($state);

        if (! $stateModel) {
            return $this;
        }

        $query = $this->states()->where('world_entity_id', $stateModel->id);

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
     * Check if state has specific meta key
     */
    public function hasStateMeta(
        State|string|int $state,
        string $key,
        ?string $group = null
    ): bool {
        $stateModel = $this->resolveState($state);

        if (! $stateModel) {
            return false;
        }

        $query = $this->states()->where('world_entity_id', $stateModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        return $pivot?->hasMeta($key) ?? false;
    }
}
