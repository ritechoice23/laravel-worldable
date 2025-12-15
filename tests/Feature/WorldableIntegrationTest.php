<?php

use Illuminate\Database\Eloquent\Model;
use Ritechoice23\Worldable\Models\City;
use Ritechoice23\Worldable\Models\Continent;
use Ritechoice23\Worldable\Models\Country;
use Ritechoice23\Worldable\Models\State;
use Ritechoice23\Worldable\Traits\Worldable;

class TestCompany extends Model
{
    use Worldable;

    protected $table = 'companies';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function () {
    Schema::create('companies', function ($table) {
        $table->id();
        $table->string('name');
    });

    $this->company = TestCompany::create(['name' => 'Acme Corp']);

    $this->continent = Continent::create(['name' => 'Europe', 'code' => 'EU']);
    $this->country = Country::create([
        'continent_id' => $this->continent->id,
        'name' => 'United Kingdom',
        'iso_code' => 'GB',
        'iso_code_3' => 'GBR',
    ]);
    $this->state = State::create([
        'country_id' => $this->country->id,
        'name' => 'England',
        'code' => 'ENG',
    ]);
    $this->city = City::create([
        'state_id' => $this->state->id,
        'name' => 'London',
        'latitude' => 51.5074,
        'longitude' => -0.1278,
    ]);
});

afterEach(function () {
    Schema::dropIfExists('companies');
});

it('can use all worldable traits at once', function () {
    $this->company->attachContinent('Europe');
    $this->company->attachCountry('United Kingdom');
    $this->company->attachState('England');
    $this->company->attachCity('London');

    expect($this->company->continents)->toHaveCount(1)
        ->and($this->company->countries)->toHaveCount(1)
        ->and($this->company->states)->toHaveCount(1)
        ->and($this->company->cities)->toHaveCount(1);
});

it('can chain attach methods fluently', function () {
    $this->company
        ->attachContinent('Europe')
        ->attachCountry('United Kingdom')
        ->attachCity('London');

    expect($this->company->continents)->toHaveCount(1)
        ->and($this->company->countries)->toHaveCount(1)
        ->and($this->company->cities)->toHaveCount(1);
});

it('can handle complex grouped scenarios', function () {
    // Headquarters in London
    $this->company->attachCountry('United Kingdom', 'headquarters');
    $this->company->attachCity('London', 'headquarters');

    // Branch in Nigeria
    $nigeriaCont = Continent::create(['name' => 'Africa', 'code' => 'AF']);
    $nigeria = Country::create([
        'continent_id' => $nigeriaCont->id,
        'name' => 'Nigeria',
        'iso_code' => 'NG',
        'iso_code_3' => 'NGA',
    ]);
    $lagos = State::create([
        'country_id' => $nigeria->id,
        'name' => 'Lagos',
        'code' => 'LA',
    ]);
    $lagosCity = City::create([
        'state_id' => $lagos->id,
        'name' => 'Lagos City',
    ]);

    $this->company->attachCountry('Nigeria', 'branch');
    $this->company->attachCity('Lagos City', 'branch');

    $hqCountry = $this->company->countries()->wherePivot('group', 'headquarters')->first();
    $branchCountry = $this->company->countries()->wherePivot('group', 'branch')->first();

    expect($hqCountry->name)->toBe('United Kingdom')
        ->and($branchCountry->name)->toBe('Nigeria')
        ->and($this->company->countries)->toHaveCount(2);
});
