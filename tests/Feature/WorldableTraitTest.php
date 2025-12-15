<?php

use Illuminate\Database\Eloquent\Model;
use Ritechoice23\Worldable\Models\Continent;
use Ritechoice23\Worldable\Models\Country;
use Ritechoice23\Worldable\Traits\HasContinent;
use Ritechoice23\Worldable\Traits\HasCountry;

// Test model
class TestUser extends Model
{
    use HasContinent, HasCountry;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function () {
    // Create users table for testing
    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
    });

    $this->user = TestUser::create(['name' => 'John Doe']);

    $this->continent = Continent::create([
        'name' => 'Africa',
        'code' => 'AF',
    ]);

    $this->country = Country::create([
        'continent_id' => $this->continent->id,
        'name' => 'Nigeria',
        'iso_code' => 'NG',
        'iso_code_3' => 'NGA',
    ]);
});

afterEach(function () {
    Schema::dropIfExists('users');
});

describe('HasCountry Trait', function () {
    it('can attach a country by name', function () {
        $this->user->attachCountry('Nigeria');

        expect($this->user->countries)->toHaveCount(1)
            ->and($this->user->countries->first()->name)->toBe('Nigeria');
    });

    it('can attach a country by ISO code', function () {
        $this->user->attachCountry('NG');

        expect($this->user->countries)->toHaveCount(1)
            ->and($this->user->countries->first()->iso_code)->toBe('NG');
    });

    it('can attach a country by 3-letter code', function () {
        $this->user->attachCountry('NGA');

        expect($this->user->countries)->toHaveCount(1)
            ->and($this->user->countries->first()->iso_code_3)->toBe('NGA');
    });

    it('can attach a country with a group', function () {
        $this->user->attachCountry('Nigeria', 'citizenship');

        $citizenship = $this->user->countries()->wherePivot('group', 'citizenship')->first();

        expect($citizenship)->not->toBeNull()
            ->and($citizenship->name)->toBe('Nigeria');
    });

    it('can detach a country', function () {
        $this->user->attachCountry('Nigeria');
        $this->user->detachCountry('Nigeria');

        expect($this->user->countries)->toHaveCount(0);
    });

    it('can sync countries', function () {
        $ghana = Country::create([
            'continent_id' => $this->continent->id,
            'name' => 'Ghana',
            'iso_code' => 'GH',
            'iso_code_3' => 'GHA',
        ]);

        $this->user->attachCountry('Nigeria');
        $this->user->syncCountries(['Ghana']);

        expect($this->user->countries)->toHaveCount(1)
            ->and($this->user->countries->first()->name)->toBe('Ghana');
    });

    it('can get country name accessor', function () {
        $this->user->attachCountry('Nigeria');

        expect($this->user->country_name)->toBe('Nigeria');
    });

    it('can get country code accessor', function () {
        $this->user->attachCountry('Nigeria');

        expect($this->user->country_code)->toBe('NG');
    });

    it('can filter users from a country', function () {
        $user2 = TestUser::create(['name' => 'Jane Doe']);

        $this->user->attachCountry('Nigeria');
        $user2->attachCountry('Nigeria');

        $nigerians = TestUser::fromCountry('Nigeria')->get();

        expect($nigerians)->toHaveCount(2);
    });
});

describe('HasContinent Trait', function () {
    it('can attach a continent', function () {
        $this->user->attachContinent('Africa');

        expect($this->user->continents)->toHaveCount(1)
            ->and($this->user->continents->first()->name)->toBe('Africa');
    });

    it('can attach a continent by code', function () {
        $this->user->attachContinent('AF');

        expect($this->user->continents)->toHaveCount(1)
            ->and($this->user->continents->first()->code)->toBe('AF');
    });

    it('can get continent name accessor', function () {
        $this->user->attachContinent('Africa');

        expect($this->user->continent_name)->toBe('Africa');
    });

    it('can filter users from a continent', function () {
        $user2 = TestUser::create(['name' => 'Jane Doe']);

        $this->user->attachContinent('Africa');
        $user2->attachContinent('Africa');

        $africans = TestUser::whereInContinent('Africa')->get();

        expect($africans)->toHaveCount(2);
    });
});
