<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ritechoice23\Worldable\Models\Timezone;
use Ritechoice23\Worldable\Traits\Concerns\InteractsWithWorldEntities;

trait HasTimezone
{
    use InteractsWithWorldEntities;

    public function timezones(): MorphToMany
    {
        $this->ensureWorldablesTableExists();

        $timezoneMorphClass = (new Timezone)->getMorphClass();

        return $this->morphToMany(Timezone::class, 'worldable', 'worldables', 'worldable_id', 'world_entity_id')
            ->withPivot('group', 'world_entity_type', 'meta')
            ->wherePivot('world_entity_type', $timezoneMorphClass)
            ->withTimestamps();
    }

    public function attachTimezone(Timezone|string|int $timezone, ?string $group = null, array $meta = []): self
    {
        return $this->attachEntity('timezones', 'resolveTimezone', $timezone, $group, $meta);
    }

    public function detachTimezone(Timezone|string|int $timezone, ?string $group = null): self
    {
        return $this->detachEntity('timezones', 'resolveTimezone', $timezone, $group);
    }

    public function syncTimezones(array $timezones, ?string $group = null, array $meta = []): self
    {
        return $this->syncEntities('timezones', 'resolveTimezone', $timezones, $group, $meta);
    }

    public function detachAllTimezones(?string $group = null): self
    {
        return $this->detachAllEntities('timezones', $group);
    }

    public function hasTimezone(Timezone|string|int $timezone, ?string $group = null): bool
    {
        return $this->hasEntity('timezones', 'resolveTimezone', $timezone, $group);
    }

    public function attachTimezones(array $timezones, ?string $group = null, array $meta = []): self
    {
        foreach ($timezones as $timezone) {
            $this->attachTimezone($timezone, $group, $meta);
        }

        return $this;
    }

    /**
     * Convert a datetime to the model's timezone
     * Speaks English: $user->localTime($event->start)
     */
    public function localTime($dateTime = null, ?string $group = null): Carbon
    {
        $dateTime = $dateTime ?? now();

        if (is_string($dateTime)) {
            $dateTime = Carbon::parse($dateTime);
        }

        $timezone = $group !== null
            ? $this->timezones()->wherePivot('group', $group)->first()
            : $this->timezones()->first();

        if ($timezone) {
            return $dateTime->setTimezone($timezone->zone_name);
        }

        return $dateTime;
    }

    // Accessors
    public function getTimezoneNameAttribute(): ?string
    {
        if ($this->relationLoaded('timezones')) {
            return $this->timezones->first()?->zone_name;
        }

        return $this->timezones()->first()?->zone_name;
    }

    public function getTimezoneAbbreviationAttribute(): ?string
    {
        if ($this->relationLoaded('timezones')) {
            return $this->timezones->first()?->abbreviation;
        }

        return $this->timezones()->first()?->abbreviation;
    }

    public function getGmtOffsetAttribute(): ?int
    {
        if ($this->relationLoaded('timezones')) {
            return $this->timezones->first()?->gmt_offset;
        }

        return $this->timezones()->first()?->gmt_offset;
    }

    public function getGmtOffsetNameAttribute(): ?string
    {
        if ($this->relationLoaded('timezones')) {
            return $this->timezones->first()?->gmt_offset_name;
        }

        return $this->timezones()->first()?->gmt_offset_name;
    }

    // Scopes
    public function scopeWhereInTimezone(Builder $query, Timezone|string|int $timezone, ?string $group = null): Builder
    {
        return $this->scopeWhereHasEntity($query, 'timezones', 'resolveTimezone', $timezone, $group);
    }

    public function scopeWhereNotInTimezone(Builder $query, Timezone|string|int $timezone, ?string $group = null): Builder
    {
        return $this->scopeWhereDoesntHaveEntity($query, 'timezones', 'resolveTimezone', $timezone, $group);
    }

    public function scopeWhereInTimezoneOffset(Builder $query, int $offsetSeconds): Builder
    {
        return $query->whereHas('timezones', function ($q) use ($offsetSeconds) {
            $q->where('gmt_offset', $offsetSeconds);
        });
    }

    public function scopeWhereInTimezoneAbbreviation(Builder $query, string $abbreviation): Builder
    {
        return $query->whereHas('timezones', function ($q) use ($abbreviation) {
            $q->where('abbreviation', strtoupper($abbreviation));
        });
    }

    protected function resolveTimezone(Timezone|string|int $value): ?Timezone
    {
        if ($value instanceof Timezone) {
            return $value;
        }

        if (is_numeric($value)) {
            return Timezone::find($value);
        }

        if (is_string($value)) {
            $timezone = Timezone::where('zone_name', $value)->first();

            if ($timezone) {
                return $timezone;
            }

            $timezone = Timezone::where('abbreviation', strtoupper($value))->first();

            if ($timezone) {
                return $timezone;
            }

            return Timezone::where('name', 'like', '%'.$value.'%')->first();
        }

        return null;
    }

    /**
     * Get meta for a specific timezone
     */
    public function getTimezoneMeta(
        Timezone|string|int $timezone,
        ?string $key = null,
        mixed $default = null,
        ?string $group = null
    ): mixed {
        $timezoneModel = $this->resolveTimezone($timezone);

        if (! $timezoneModel) {
            return $default;
        }

        $query = $this->timezones()->where('world_entity_id', $timezoneModel->id);

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
     * Update meta for a specific timezone
     */
    public function updateTimezoneMeta(
        Timezone|string|int $timezone,
        array $meta,
        ?string $group = null,
        bool $merge = true
    ): self {
        $timezoneModel = $this->resolveTimezone($timezone);

        if (! $timezoneModel) {
            return $this;
        }

        $query = $this->timezones()->where('world_entity_id', $timezoneModel->id);

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
     * Check if timezone has specific meta key
     */
    public function hasTimezoneMeta(
        Timezone|string|int $timezone,
        string $key,
        ?string $group = null
    ): bool {
        $timezoneModel = $this->resolveTimezone($timezone);

        if (! $timezoneModel) {
            return false;
        }

        $query = $this->timezones()->where('world_entity_id', $timezoneModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        return $pivot?->hasMeta($key) ?? false;
    }
}
