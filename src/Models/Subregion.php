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
 * Subregion Model
 *
 * @property int $id
 * @property int $continent_id
 * @property string $name
 * @property string $code
 * @property array<string, mixed>|null $data
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Subregion extends Model
{
    use HasFactory;
    use ManagesForeignKeys;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('worldable.tables.subregions', 'world_subregions');
    }

    protected $fillable = [
        'continent_id',
        'name',
        'code',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'continent_id' => 'integer',
    ];

    protected function getForeignKeyDefinitions(): array
    {
        return [
            'continent_id' => [
                'table' => config('worldable.tables.continents', 'world_continents'),
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

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    /**
     * @param  Builder<Subregion>  $query
     * @return Builder<Subregion>
     */
    public function scopeWhereCode(Builder $query, string $code): Builder
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * @param  Builder<Subregion>  $query
     * @return Builder<Subregion>
     */
    public function scopeWhereName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }

    /**
     * @param  Builder<Subregion>  $query
     * @return Builder<Subregion>
     */
    public function scopeOfContinent(Builder $query, Continent|string|int $continent): Builder
    {
        if ($continent instanceof Continent) {
            return $query->where('continent_id', $continent->id);
        }

        if (is_numeric($continent)) {
            return $query->where('continent_id', $continent);
        }

        return $query->whereHas('continent', function (Builder $q) use ($continent): void {
            $q->where('name', $continent)->orWhere('code', strtoupper($continent));
        });
    }
}
