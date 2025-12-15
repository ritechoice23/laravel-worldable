<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Ritechoice23\Worldable\Exceptions\DependencyNotInstalledException;
use Ritechoice23\Worldable\Traits\ManagesForeignKeys;

/**
 * State Model
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string|null $code
 * @property array<string, mixed>|null $metadata Contains: latitude, longitude, and other data
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class State extends Model
{
    use HasFactory;
    use ManagesForeignKeys;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('worldable.tables.states', 'world_states');
    }

    protected $fillable = [
        'country_id',
        'name',
        'code',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    protected function getForeignKeyDefinitions(): array
    {
        return [
            'country_id' => [
                'table' => config('worldable.tables.countries', 'world_countries'),
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

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * @param  Builder<State>  $query
     * @return Builder<State>
     */
    public function scopeWhereCode(Builder $query, string $code): Builder
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * @param  Builder<State>  $query
     * @return Builder<State>
     */
    public function scopeWhereName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }
}
