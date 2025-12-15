<?php

use Ritechoice23\Worldable\Models\Continent;
use Ritechoice23\Worldable\Models\Country;

beforeEach(function () {
    $this->continent = Continent::create([
        'name' => 'Africa',
        'code' => 'AF',
    ]);
});

it('can create a continent', function () {
    expect($this->continent)->toBeInstanceOf(Continent::class)
        ->and($this->continent->name)->toBe('Africa')
        ->and($this->continent->code)->toBe('AF');
});

it('has countries relationship', function () {
    $country = Country::create([
        'continent_id' => $this->continent->id,
        'name' => 'Nigeria',
        'iso_code' => 'NG',
        'iso_code_3' => 'NGA',
    ]);

    expect($this->continent->countries)->toHaveCount(1)
        ->and($this->continent->countries->first()->name)->toBe('Nigeria');
});

it('can find continent by code', function () {
    $found = Continent::whereCode('AF')->first();

    expect($found)->not->toBeNull()
        ->and($found->name)->toBe('Africa');
});

it('can find continent by name', function () {
    $found = Continent::whereName('Africa')->first();

    expect($found)->not->toBeNull()
        ->and($found->code)->toBe('AF');
});

it('can search continent by partial name', function () {
    $found = Continent::whereName('Afr')->first();

    expect($found)->not->toBeNull()
        ->and($found->name)->toBe('Africa');
});
