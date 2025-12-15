<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Ritechoice23\Worldable\Exceptions\DependencyNotInstalledException;
use Ritechoice23\Worldable\Traits\ManagesForeignKeys;

/**
 * City Model
 *
 * @property int $id
 * @property int $state_id
 * @property string $name
 * @property float|null $latitude
 * @property float|null $longitude
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class City extends Model
{
    use HasFactory;
    use ManagesForeignKeys;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('worldable.tables.cities', 'world_cities');
    }

    protected $fillable = [
        'country_id',
        'state_id',
        'name',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected function getForeignKeyDefinitions(): array
    {
        return [
            'country_id' => [
                'table' => config('worldable.tables.countries', 'world_countries'),
            ],
            'state_id' => [
                'table' => config('worldable.tables.states', 'world_states'),
            ],
        ];
    }

    public function country(): BelongsTo
    {
        if (! Schema::hasTable(config('worldable.tables.countries', 'world_countries'))) {
            throw DependencyNotInstalledException::forRelationship(
                self::class,
                'country',
                'countries'
            );
        }

        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        if (! Schema::hasTable(config('worldable.tables.states', 'world_states'))) {
            throw DependencyNotInstalledException::forRelationship(
                self::class,
                'state',
                'states'
            );
        }

        return $this->belongsTo(State::class);
    }

    /**
     * @param  Builder<City>  $query
     * @return Builder<City>
     */
    public function scopeWhereName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }
}
