<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Ritechoice23\Worldable\Models\Language;
use Ritechoice23\Worldable\Traits\Concerns\InteractsWithWorldEntities;

trait HasLanguage
{
    use InteractsWithWorldEntities;

    public function languages(): MorphToMany
    {
        $this->ensureWorldablesTableExists();

        $languageMorphClass = (new Language)->getMorphClass();

        return $this->morphToMany(Language::class, 'worldable', 'worldables', 'worldable_id', 'world_entity_id')
            ->withPivot('group', 'world_entity_type', 'meta')
            ->wherePivot('world_entity_type', $languageMorphClass)
            ->withTimestamps();
    }

    public function attachLanguage(Language|string|int $language, ?string $group = null, array $meta = []): self
    {
        return $this->attachEntity('languages', 'resolveLanguage', $language, $group, $meta);
    }

    public function detachLanguage(Language|string|int $language, ?string $group = null): self
    {
        return $this->detachEntity('languages', 'resolveLanguage', $language, $group);
    }

    public function syncLanguages(array $languages, ?string $group = null, array $meta = []): self
    {
        return $this->syncEntities('languages', 'resolveLanguage', $languages, $group, $meta);
    }

    public function detachAllLanguages(?string $group = null): self
    {
        return $this->detachAllEntities('languages', $group);
    }

    public function hasLanguage(Language|string|int $language, ?string $group = null): bool
    {
        return $this->hasEntity('languages', 'resolveLanguage', $language, $group);
    }

    public function attachLanguages(array $languages, ?string $group = null, array $meta = []): self
    {
        foreach ($languages as $language) {
            $this->attachLanguage($language, $group, $meta);
        }

        return $this;
    }

    // Accessors
    public function getLanguageNameAttribute(): ?string
    {
        if ($this->relationLoaded('languages')) {
            return $this->languages->first()?->name;
        }

        return $this->languages()->first()?->name;
    }

    public function getLanguageCodeAttribute(): ?string
    {
        if ($this->relationLoaded('languages')) {
            return $this->languages->first()?->iso_code;
        }

        return $this->languages()->first()?->iso_code;
    }

    public function getLanguageNativeNameAttribute(): ?string
    {
        if ($this->relationLoaded('languages')) {
            return $this->languages->first()?->native_name;
        }

        return $this->languages()->first()?->native_name;
    }

    // Scopes
    public function scopeWhereSpeaks(Builder $query, Language|string|int $language, ?string $group = null): Builder
    {
        return $this->scopeWhereHasEntity($query, 'languages', 'resolveLanguage', $language, $group);
    }

    public function scopeWhereNotSpeaks(Builder $query, Language|string|int $language, ?string $group = null): Builder
    {
        return $this->scopeWhereDoesntHaveEntity($query, 'languages', 'resolveLanguage', $language, $group);
    }

    public function scopeWhereLanguage(Builder $query, Language|string|int $language, ?string $group = null): Builder
    {
        return $this->scopeWhereSpeaks($query, $language, $group);
    }

    protected function resolveLanguage(Language|string|int $value): ?Language
    {
        if ($value instanceof Language) {
            return $value;
        }

        if (is_numeric($value)) {
            return Language::find($value);
        }

        if (is_string($value)) {
            return Language::where('name', $value)
                ->orWhere('iso_code', strtolower($value))
                ->first();
        }

        return null;
    }

    /**
     * Get meta for a specific language
     */
    public function getLanguageMeta(
        Language|string|int $language,
        ?string $key = null,
        mixed $default = null,
        ?string $group = null
    ): mixed {
        $languageModel = $this->resolveLanguage($language);

        if (! $languageModel) {
            return $default;
        }

        $query = $this->languages()->where('world_entity_id', $languageModel->id);

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
     * Update meta for a specific language
     */
    public function updateLanguageMeta(
        Language|string|int $language,
        array $meta,
        ?string $group = null,
        bool $merge = true
    ): self {
        $languageModel = $this->resolveLanguage($language);

        if (! $languageModel) {
            return $this;
        }

        $query = $this->languages()->where('world_entity_id', $languageModel->id);

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
     * Check if language has specific meta key
     */
    public function hasLanguageMeta(
        Language|string|int $language,
        string $key,
        ?string $group = null
    ): bool {
        $languageModel = $this->resolveLanguage($language);

        if (! $languageModel) {
            return false;
        }

        $query = $this->languages()->where('world_entity_id', $languageModel->id);

        if ($group !== null) {
            $query->wherePivot('group', $group);
        }

        $pivot = $query->first()?->pivot;

        return $pivot?->hasMeta($key) ?? false;
    }
}
