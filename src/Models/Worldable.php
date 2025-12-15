<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Worldable Pivot Model
 *
 * Polymorphic pivot model connecting any model to world entities
 *
 * @property int $id
 * @property string $worldable_type
 * @property int $worldable_id
 * @property string $world_entity_type
 * @property int $world_entity_id
 * @property string|null $group
 * @property array|null $meta
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Worldable extends Model
{
    protected $table;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = (string) config('worldable.tables.worldables', 'worldables');
    }

    /** @var list<string> */
    protected $fillable = [
        'worldable_type',
        'worldable_id',
        'world_entity_type',
        'world_entity_id',
        'group',
        'meta',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Get the owning worldable model (User, Team, Product, etc.)
     */
    public function worldable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the world entity (Country, City, Currency, etc.)
     */
    public function worldEntity(): MorphTo
    {
        return $this->morphTo('world_entity');
    }

    /**
     * Get meta data or specific key
     */
    public function getMeta(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->meta ?? [];
        }

        return data_get($this->meta, $key, $default);
    }

    /**
     * Set meta data
     */
    public function setMeta(string|array $key, mixed $value = null): self
    {
        if (is_array($key)) {
            $this->meta = $key;
        } else {
            $meta = $this->meta ?? [];
            data_set($meta, $key, $value);
            $this->meta = $meta;
        }

        return $this;
    }

    /**
     * Check if meta key exists
     */
    public function hasMeta(string $key): bool
    {
        return data_get($this->meta, $key) !== null;
    }

    /**
     * Merge meta data
     */
    public function mergeMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta ?? [], $meta);

        return $this;
    }

    /**
     * Remove meta key
     */
    public function removeMeta(string $key): self
    {
        $meta = $this->meta ?? [];
        data_forget($meta, $key);
        $this->meta = $meta;

        return $this;
    }
}
