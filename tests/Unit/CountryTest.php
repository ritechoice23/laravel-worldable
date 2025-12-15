<?php

use Ritechoice23\Worldable\Models\Continent;
use Ritechoice23\Worldable\Models\Country;
use Ritechoice23\Worldable\Models\State;

beforeEach(function () {
    $this->continent = Continent::create([
        'name' => 'Africa',
        'code' => 'AF',
    ]);

    $this->country = Country::create([
        'continent_id' => $this->continent->id,
        'name' => 'Nigeria',
        'iso_code' => 'NG',
        'iso_code_3' => 'NGA',
        'calling_code' => '+234',
        'currency_code' => 'NGN',
        'metadata' => ['flag' => '🇳🇬'],
    ]);
});

it('can create a country', function () {
    expect($this->country)->toBeInstanceOf(Country::class)
        ->and($this->country->name)->toBe('Nigeria')
        ->and($this->country->iso_code)->toBe('NG')
        ->and($this->country->iso_code_3)->toBe('NGA');
});

it('belongs to a continent', function () {
    expect($this->country->continent)->toBeInstanceOf(Continent::class)
        ->and($this->country->continent->name)->toBe('Africa');
});

it('has states relationship', function () {
    $state = State::create([
        'country_id' => $this->country->id,
        'name' => 'Lagos',
        'code' => 'LA',
    ]);

    expect($this->country->states)->toHaveCount(1)
        ->and($this->country->states->first()->name)->toBe('Lagos');
});

it('can find country by 2-letter code', function () {
    $found = Country::whereCode('NG')->first();

    expect($found)->not->toBeNull()
        ->and($found->name)->toBe('Nigeria');
});

it('can find country by 3-letter code', function () {
    $found = Country::whereCode('NGA')->first();

    expect($found)->not->toBeNull()
        ->and($found->name)->toBe('Nigeria');
});

it('can find country by name', function () {
    $found = Country::whereName('Nigeria')->first();

    expect($found)->not->toBeNull()
        ->and($found->iso_code)->toBe('NG');
});

it('stores metadata as array', function () {
    expect($this->country->metadata)->toBeArray()
        ->and($this->country->metadata['flag'])->toBe('🇳🇬');
});
