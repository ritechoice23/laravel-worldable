<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Timezone Model
 *
 * @property int $id
 * @property int|null $continent_id
 * @property string $name
 * @property string $zone_name
 * @property int $gmt_offset
 * @property string $gmt_offset_name
 * @property string $abbreviation
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Timezone extends Model
{
    use HasFactory;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('worldable.tables.timezones', 'world_timezones');
    }

    protected $fillable = [
        'continent_id',
        'name',
        'zone_name',
        'gmt_offset',
        'gmt_offset_name',
        'abbreviation',
    ];

    protected function casts(): array
    {
        return [
            'gmt_offset' => 'integer',
        ];
    }

    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    /**
     * @param  Builder<Timezone>  $query
     * @return Builder<Timezone>
     */
    public function scopeWhereName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }

    /**
     * @param  Builder<Timezone>  $query
     * @return Builder<Timezone>
     */
    public function scopeWhereZone(Builder $query, string $zoneName): Builder
    {
        return $query->where('zone_name', $zoneName);
    }

    /**
     * @param  Builder<Timezone>  $query
     * @return Builder<Timezone>
     */
    public function scopeWhereAbbreviation(Builder $query, string $abbreviation): Builder
    {
        return $query->where('abbreviation', strtoupper($abbreviation));
    }

    /**
     * @param  Builder<Timezone>  $query
     * @return Builder<Timezone>
     */
    public function scopeWhereOffset(Builder $query, int $offset): Builder
    {
        return $query->where('gmt_offset', $offset);
    }
}
