<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Language Model
 *
 * @property int $id
 * @property string $name
 * @property string|null $native_name
 * @property string $iso_code
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Language extends Model
{
    use HasFactory;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('worldable.tables.languages', 'world_languages');
    }

    protected $fillable = [
        'name',
        'native_name',
        'iso_code',
    ];

    /**
     * @param  Builder<Language>  $query
     * @return Builder<Language>
     */
    public function scopeWhereCode(Builder $query, string $code): Builder
    {
        return $query->where('iso_code', strtolower($code));
    }

    /**
     * @param  Builder<Language>  $query
     * @return Builder<Language>
     */
    public function scopeWhereName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }
}
