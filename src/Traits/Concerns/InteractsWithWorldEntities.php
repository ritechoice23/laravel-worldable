<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Base trait for world entity interactions
 *
 * Provides common functionality for attach, detach, sync, and resolve operations
 * that are shared across all world entity traits.
 */
trait InteractsWithWorldEntities
{
    /**
     * Ensure the worldables pivot table exists
     *
     * @throws RuntimeException
     */
    protected function ensureWorldablesTableExists(): void
    {
        $tableName = config('worldable.tables.worldables', 'worldables');

        if (! Schema::hasTable($tableName)) {
            throw new RuntimeException(
                "The '{$tableName}' table does not exist. ".
                "Please run 'php artisan world:install --worldables' to create the worldables pivot table."
            );
        }
    }

    /**
     * Generic attach method for world entities
     */
    protected function attachEntity(
        string $relationshipName,
        string $resolveMethod,
        Model|string|int $entity,
        ?string $group = null,
        array $meta = []
    ): self {
        $entityModel = $this->{$resolveMethod}($entity);

        if ($entityModel) {
            $this->{$relationshipName}()->syncWithoutDetaching([
                $entityModel->id => [
                    'group' => $group,
                    'world_entity_type' => $this->getEntityMorphClass($entityModel),
                    'meta' => empty($meta) ? null : json_encode($meta),
                ],
            ]);

            // Refresh the relationship to ensure it's loaded from database
            $this->unsetRelation($relationshipName);
        }

        return $this;
    }

    /**
     * Generic detach method for world entities
     */
    protected function detachEntity(
        string $relationshipName,
        string $resolveMethod,
        Model|string|int $entity,
        ?string $group = null
    ): self {
        $entityModel = $this->{$resolveMethod}($entity);

        if ($entityModel) {
            $query = $this->{$relationshipName}()->wherePivot('world_entity_id', $entityModel->id);

            if ($group !== null) {
                $query->wherePivot('group', $group);
            }

            $query->detach();
        }

        return $this;
    }

    /**
     * Generic sync method for world entities
     */
    protected function syncEntities(
        string $relationshipName,
        string $resolveMethod,
        array $entities,
        ?string $group = null,
        array $meta = []
    ): self {
        $entityData = collect($entities)
            ->map(fn ($entity) => $this->{$resolveMethod}($entity))
            ->filter()
            ->mapWithKeys(fn ($model) => [
                $model->id => [
                    'group' => $group,
                    'world_entity_type' => $this->getEntityMorphClass($model),
                    'meta' => empty($meta) ? null : json_encode($meta),
                ],
            ])
            ->toArray();

        if ($group !== null) {
            // Get ALL existing pivot records
            $allPivots = \DB::table('worldables')
                ->where('worldable_type', $this->getMorphClass())
                ->where('worldable_id', $this->id)
                ->get();

            // First, detach all entities in the target group
            $entitiesToDetach = $allPivots
                ->filter(fn ($pivot) => $pivot->group === $group)
                ->pluck('world_entity_id')
                ->unique()
                ->toArray();

            if (! empty($entitiesToDetach)) {
                \DB::table('worldables')
                    ->where('worldable_type', $this->getMorphClass())
                    ->where('worldable_id', $this->id)
                    ->where('group', $group)
                    ->delete();
            }

            // Then attach the new entities for the target group
            foreach ($entityData as $entityId => $pivotData) {
                \DB::table('worldables')->insert([
                    'worldable_type' => $this->getMorphClass(),
                    'worldable_id' => $this->id,
                    'world_entity_id' => $entityId,
                    'world_entity_type' => $pivotData['world_entity_type'],
                    'group' => $pivotData['group'],
                    'meta' => $pivotData['meta'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            $this->{$relationshipName}()->sync($entityData);
        }

        // Refresh the relationship to ensure it's loaded from database
        $this->unsetRelation($relationshipName);

        return $this;
    }

    /**
     * Generic detach all method for world entities
     */
    protected function detachAllEntities(string $relationshipName, ?string $group = null): self
    {
        $query = $this->{$relationshipName}();

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $query->detach();

        return $this;
    }

    /**
     * Generic scope for checking entity relationship
     */
    protected function scopeWhereHasEntity(
        Builder $query,
        string $relationshipName,
        string $resolveMethod,
        Model|string|int $entity,
        ?string $group = null
    ): Builder {
        // Create a temporary instance to call the resolve method (scopes are static)
        $tempInstance = new static;

        // Resolve entity outside the closure
        $entityModel = is_string($entity) || is_int($entity)
            ? $tempInstance->{$resolveMethod}($entity)
            : $entity;

        return $query->whereHas($relationshipName, function ($q) use ($entityModel, $group) {
            if ($entityModel) {
                $q->where('world_entity_id', $entityModel->id);
            }

            if ($group !== null) {
                $q->where('worldables.group', $group);
            }
        });
    }

    /**
     * Generic scope for checking entity does not have relationship
     */
    protected function scopeWhereDoesntHaveEntity(
        Builder $query,
        string $relationshipName,
        string $resolveMethod,
        Model|string|int $entity,
        ?string $group = null
    ): Builder {
        // Create a temporary instance to call the resolve method (scopes are static)
        $tempInstance = new static;

        // Resolve entity outside the closure
        $entityModel = is_string($entity) || is_int($entity)
            ? $tempInstance->{$resolveMethod}($entity)
            : $entity;

        return $query->whereDoesntHave($relationshipName, function ($q) use ($entityModel, $group) {
            if ($entityModel) {
                $q->where('world_entity_id', $entityModel->id);
            }

            if ($group !== null) {
                $q->where('worldables.group', $group);
            }
        });
    }

    /**
     * Generic method to check if entity is attached
     */
    protected function hasEntity(
        string $relationshipName,
        string $resolveMethod,
        Model|string|int $entity,
        ?string $group = null
    ): bool {
        $entityModel = $this->{$resolveMethod}($entity);

        if (! $entityModel) {
            return false;
        }

        $query = $this->{$relationshipName}()->where('world_entity_id', $entityModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        return $query->exists();
    }

    /**
     * Generic bulk attach method for world entities
     */
    protected function attachEntities(
        string $attachMethodName,
        array $entities,
        ?string $group = null
    ): self {
        foreach ($entities as $entity) {
            $this->{$attachMethodName}($entity, $group);
        }

        return $this;
    }

    /**
     * Get the morph class name for a model, respecting morphMap
     */
    protected function getEntityMorphClass(Model $model): string
    {
        $morphMap = Relation::morphMap();

        if (! empty($morphMap)) {
            $modelClass = get_class($model);
            $alias = array_search($modelClass, $morphMap, true);

            if ($alias !== false) {
                return $alias;
            }
        }

        return $model->getMorphClass();
    }
}
