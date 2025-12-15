<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ritechoice23\Worldable\Models\Currency;
use Ritechoice23\Worldable\Traits\Concerns\InteractsWithWorldEntities;

trait HasCurrency
{
    use InteractsWithWorldEntities;

    public function currencies(): MorphToMany
    {
        $this->ensureWorldablesTableExists();

        $currencyMorphClass = (new Currency)->getMorphClass();

        return $this->morphToMany(Currency::class, 'worldable', 'worldables', 'worldable_id', 'world_entity_id')
            ->withPivot('group', 'world_entity_type', 'meta')
            ->wherePivot('world_entity_type', $currencyMorphClass)
            ->withTimestamps();
    }

    public function attachCurrency(Currency|string|int $currency, ?string $group = null, array $meta = []): self
    {
        return $this->attachEntity('currencies', 'resolveCurrency', $currency, $group, $meta);
    }

    public function detachCurrency(Currency|string|int $currency, ?string $group = null): self
    {
        return $this->detachEntity('currencies', 'resolveCurrency', $currency, $group);
    }

    public function syncCurrencies(array $currencies, ?string $group = null): self
    {
        return $this->syncEntities('currencies', 'resolveCurrency', $currencies, $group, $meta);
    }

    public function detachAllCurrencies(?string $group = null): self
    {
        return $this->detachAllEntities('currencies', $group);
    }

    public function hasCurrency(Currency|string|int $currency, ?string $group = null): bool
    {
        return $this->hasEntity('currencies', 'resolveCurrency', $currency, $group);
    }

    public function attachCurrencies(array $currencies, ?string $group = null, array $meta = []): self
    {
        foreach ($currencies as $currency) {
            $this->attachCurrency($currency, $group, $meta);
        }

        return $this;
    }

    // Accessors
    public function getCurrencySymbolAttribute(): ?string
    {
        if ($this->relationLoaded('currencies')) {
            return $this->currencies->first()?->symbol;
        }

        return $this->currencies()->first()?->symbol;
    }

    public function getCurrencyCodeAttribute(): ?string
    {
        if ($this->relationLoaded('currencies')) {
            return $this->currencies->first()?->code;
        }

        return $this->currencies()->first()?->code;
    }

    public function getCurrencyNameAttribute(): ?string
    {
        if ($this->relationLoaded('currencies')) {
            return $this->currencies->first()?->name;
        }

        return $this->currencies()->first()?->name;
    }

    // Financial utilities
    public function formatMoney(float $amount, ?string $group = null): string
    {
        $currency = $group !== null
            ? $this->currencies()->wherePivot('group', $group)->first()
            : $this->currencies()->first();

        if ($currency) {
            return $currency->format($amount);
        }

        return number_format($amount, 2);
    }

    public function formatPrice(float $amount, ?string $group = null): string
    {
        return $this->formatMoney($amount, $group);
    }

    // Scopes
    public function scopeWherePricedIn(Builder $query, Currency|string|int $currency, ?string $group = null): Builder
    {
        return $this->scopeWhereHasEntity($query, 'currencies', 'resolveCurrency', $currency, $group);
    }

    public function scopeWhereCurrency(Builder $query, Currency|string|int $currency, ?string $group = null): Builder
    {
        return $this->scopeWherePricedIn($query, $currency, $group);
    }

    public function scopeWhereNotPricedIn(Builder $query, Currency|string|int $currency, ?string $group = null): Builder
    {
        return $this->scopeWhereDoesntHaveEntity($query, 'currencies', 'resolveCurrency', $currency, $group);
    }

    public function scopePricedIn(Builder $query, Currency|string|int $currency, ?string $group = null): Builder
    {
        return $this->scopeWherePricedIn($query, $currency, $group);
    }

    protected function resolveCurrency(Currency|string|int $value): ?Currency
    {
        if ($value instanceof Currency) {
            return $value;
        }

        if (is_numeric($value)) {
            return Currency::find($value);
        }

        if (is_string($value)) {
            return Currency::where('code', strtoupper($value))
                ->orWhere('name', $value)
                ->first();
        }

        return null;
    }

    /**
     * Get meta for a specific currency
     */
    public function getCurrencyMeta(
        Currency|string|int $currency,
        ?string $key = null,
        mixed $default = null,
        ?string $group = null
    ): mixed {
        $currencyModel = $this->resolveCurrency($currency);

        if (! $currencyModel) {
            return $default;
        }

        $query = $this->currencys()->where('world_entity_id', $currencyModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        if (! $pivot) {
            return $default;
        }

        return $pivot->getMeta($key, $default);
    }

    /**
     * Update meta for a specific currency
     */
    public function updateCurrencyMeta(
        Currency|string|int $currency,
        array $meta,
        ?string $group = null,
        bool $merge = true
    ): self {
        $currencyModel = $this->resolveCurrency($currency);

        if (! $currencyModel) {
            return $this;
        }

        $query = $this->currencys()->where('world_entity_id', $currencyModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        if ($pivot) {
            if ($merge) {
                $pivot->mergeMeta($meta);
            } else {
                $pivot->setMeta($meta);
            }
            $pivot->save();
        }

        return $this;
    }

    /**
     * Check if currency has specific meta key
     */
    public function hasCurrencyMeta(
        Currency|string|int $currency,
        string $key,
        ?string $group = null
    ): bool {
        $currencyModel = $this->resolveCurrency($currency);

        if (! $currencyModel) {
            return false;
        }

        $query = $this->currencys()->where('world_entity_id', $currencyModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        return $pivot?->hasMeta($key) ?? false;
    }
}
