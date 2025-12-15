<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable\Traits;

/**
 * Worldable Trait
 *
 * Master trait for adding all world functionality to models.
 * Includes all individual traits: HasContinent, HasSubregion, HasCountry, HasState, HasCity, HasCurrency, HasLanguage, HasTimezone.
 */
trait Worldable
{
    use HasCity;
    use HasContinent;
    use HasCountry;
    use HasCurrency;
    use HasLanguage;
    use HasState;
    use HasSubregion;
    use HasTimezone;
}
