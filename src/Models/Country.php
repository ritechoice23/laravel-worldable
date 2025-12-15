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
 * Country Model
 *
 * @property int $id
 * @property int $continent_id
 * @property int|null $subregion_id
 * @property string $name
 * @property string $iso_code
 * @property string|null $iso_code_3
 * @property string|null $calling_code
 * @property array<string, mixed>|null $metadata Contains: latitude, longitude, timezones[], currencies[], capital, native, etc.
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Country extends Model
{
    use HasFactory;
    use ManagesForeignKeys;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('worldable.tables.countries', 'world_countries');
    }

    protected $fillable = [
        'continent_id',
        'subregion_id',
        'name',
        'iso_code',
        'iso_code_3',
        'calling_code',
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
            'continent_id' => [
                'table' => config('worldable.tables.continents', 'world_continents'),
            ],
            'subregion_id' => [
                'table' => config('worldable.tables.subregions', 'world_subregions'),
            ],
        ];
    }

    public function continent(): BelongsTo
    {
        if (! Schema::hasTable(config('worldable.tables.continents', 'world_continents'))) {
            throw DependencyNotInstalledException::forRelationship(
                self::class,
                'continent',
                'continents'
            );
        }

        return $this->belongsTo(Continent::class);
    }

    public function subregion(): BelongsTo
    {
        if (! Schema::hasTable(config('worldable.tables.subregions', 'world_subregions'))) {
            throw DependencyNotInstalledException::forRelationship(
                self::class,
                'subregion',
                'subregions'
            );
        }

        return $this->belongsTo(Subregion::class);
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function cities(): HasMany
    {
        if (! Schema::hasTable(config('worldable.tables.cities', 'world_cities'))) {
            throw DependencyNotInstalledException::forRelationship(
                self::class,
                'city',
                'cities'
            );
        }

        return $this->hasMany(City::class);
    }

    /**
     * Get capital from metadata
     */
    public function getCapitalAttribute(): ?string
    {
        return $this->metadata['capital'] ?? null;
    }

    /**
     * @param  Builder<Country>  $query
     * @return Builder<Country>
     */
    public function scopeWhereCode(Builder $query, string $code): Builder
    {
        $code = strtoupper($code);

        return $query->where('iso_code', $code)
            ->orWhere('iso_code_3', $code);
    }

    /**
     * @param  Builder<Country>  $query
     * @return Builder<Country>
     */
    public function scopeWhereName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }
}
