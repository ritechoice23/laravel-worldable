<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Currency Model
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $symbol
 * @property string|null $symbol_native
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Currency extends Model
{
    use HasFactory;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('worldable.tables.currencies', 'world_currencies');
    }

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'symbol_native',
    ];

    /**
     * @param  Builder<Currency>  $query
     * @return Builder<Currency>
     */
    public function scopeWhereCode(Builder $query, string $code): Builder
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * @param  Builder<Currency>  $query
     * @return Builder<Currency>
     */
    public function scopeWhereName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }

    /**
     * Format an amount in this currency
     */
    public function format(
        float $amount,
        ?int $decimals = null,
        bool $withSpace = false,
        ?string $locale = null
    ): string {
        $decimals = $decimals ?? 2;
        $symbol = $this->symbol ?? $this->code;
        $space = $withSpace ? ' ' : '';

        // Use locale-specific formatting if requested
        if ($locale !== null && class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

            return $formatter->formatCurrency($amount, $this->code);
        }

        // Handle negative amounts
        if ($amount < 0) {
            $formatted = number_format(abs($amount), $decimals);

            return "-{$symbol}{$space}{$formatted}";
        }

        $formatted = number_format($amount, $decimals);

        return "{$symbol}{$space}{$formatted}";
    }
}
