<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Continent Model
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Continent extends Model
{
    use HasFactory;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('worldable.tables.continents', 'world_continents');
    }

    protected $fillable = [
        'name',
        'code',
    ];

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }

    public function subregions(): HasMany
    {
        return $this->hasMany(Subregion::class);
    }

    /**
     * @param  Builder<Continent>  $query
     * @return Builder<Continent>
     */
    public function scopeWhereCode(Builder $query, string $code): Builder
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * @param  Builder<Continent>  $query
     * @return Builder<Continent>
     */
    public function scopeWhereName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }
}
