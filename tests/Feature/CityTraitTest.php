<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Ritechoice23\Worldable\Models\City;
use Ritechoice23\Worldable\Models\Country;
use Ritechoice23\Worldable\Models\State;
use Ritechoice23\Worldable\Traits\HasCity;

class TestUserWithCity extends Model
{
    use HasCity;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function () {
    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
    });

    $this->user = TestUserWithCity::create([
        'name' => 'Test User',
    ]);

    $this->country = Country::create([
        'name' => 'United States',
        'iso_code' => 'US',
        'iso_code_3' => 'USA',
        'continent_id' => 1,
    ]);

    $this->state = State::create([
        'name' => 'California',
        'code' => 'CA',
        'country_id' => $this->country->id,
    ]);

    $this->losAngeles = City::create([
        'name' => 'Los Angeles',
        'state_id' => $this->state->id,
        'latitude' => 34.052235,
        'longitude' => -118.243683,
    ]);

    $this->sanFrancisco = City::create([
        'name' => 'San Francisco',
        'state_id' => $this->state->id,
        'latitude' => 37.774929,
        'longitude' => -122.419418,
    ]);
});

afterEach(function () {
    Schema::dropIfExists('users');
});

it('can attach a city', function () {
    $this->user->attachCity('Los Angeles');

    expect($this->user->cities)->toHaveCount(1)
        ->and($this->user->cities->first()->name)->toBe('Los Angeles');
});

it('can attach city by model instance', function () {
    $this->user->attachCity($this->losAngeles);

    expect($this->user->cities->first()->id)->toBe($this->losAngeles->id);
});

it('can attach city by ID', function () {
    $this->user->attachCity($this->losAngeles->id);

    expect($this->user->cities->first()->id)->toBe($this->losAngeles->id);
});

it('can check if user has city', function () {
    $this->user->attachCity('Los Angeles');

    expect($this->user->hasCity('Los Angeles'))->toBeTrue()
        ->and($this->user->hasCity('San Francisco'))->toBeFalse();
});

it('can attach multiple cities at once', function () {
    $this->user->attachCities(['Los Angeles', 'San Francisco']);

    expect($this->user->cities)->toHaveCount(2);
});

it('can attach cities with bulk operation', function () {
    $this->user->attachCities(['Los Angeles', 'San Francisco'], 'visited');

    $visited = $this->user->cities()->wherePivot('group', 'visited')->get();

    expect($visited)->toHaveCount(2);
});

it('can filter users by city using whereInCity', function () {
    $user2 = TestUserWithCity::create(['name' => 'User 2']);

    $this->user->attachCity('Los Angeles');
    $user2->attachCity('San Francisco');

    $laUsers = TestUserWithCity::whereInCity('Los Angeles')->get();

    expect($laUsers)->toHaveCount(1)
        ->and($laUsers->first()->name)->toBe('Test User');
});

it('can filter users excluding city using whereNotInCity', function () {
    $user2 = TestUserWithCity::create(['name' => 'User 2']);

    $this->user->attachCity('Los Angeles');
    $user2->attachCity('San Francisco');

    $nonLAUsers = TestUserWithCity::whereNotInCity('Los Angeles')->get();

    expect($nonLAUsers)->toHaveCount(1)
        ->and($nonLAUsers->first()->name)->toBe('User 2');
});

it('can handle multiple cities with groups', function () {
    $this->user->attachCity('Los Angeles', 'residence');
    $this->user->attachCity('San Francisco', 'workplace');

    $residenceCity = $this->user->cities()->wherePivot('group', 'residence')->first();
    $workplaceCity = $this->user->cities()->wherePivot('group', 'workplace')->first();

    expect($residenceCity->name)->toBe('Los Angeles')
        ->and($workplaceCity->name)->toBe('San Francisco');
});

it('can check city in specific group', function () {
    $this->user->attachCity('Los Angeles', 'residence');
    $this->user->attachCity('San Francisco', 'workplace');

    expect($this->user->hasCity('Los Angeles', 'residence'))->toBeTrue()
        ->and($this->user->hasCity('Los Angeles', 'workplace'))->toBeFalse();
});

it('can filter by city and group', function () {
    $user2 = TestUserWithCity::create(['name' => 'User 2']);

    $this->user->attachCity('Los Angeles', 'residence');
    $user2->attachCity('Los Angeles', 'workplace');

    $residents = TestUserWithCity::whereInCity('Los Angeles', 'residence')->get();

    expect($residents)->toHaveCount(1)
        ->and($residents->first()->name)->toBe('Test User');
});

it('can access city coordinates', function () {
    $this->user->attachCity('Los Angeles');

    $city = $this->user->cities->first();

    expect($city->latitude)->toBe(34.052235)
        ->and($city->longitude)->toBe(-118.243683);
});

it('can sync cities', function () {
    $this->user->attachCity('Los Angeles');
    $this->user->attachCity('San Francisco');

    $this->user->syncCities(['Los Angeles']);

    expect($this->user->cities)->toHaveCount(1)
        ->and($this->user->cities->first()->name)->toBe('Los Angeles');
});

it('can sync cities for specific group', function () {
    $this->user->attachCity('Los Angeles', 'residence');
    $this->user->attachCity('San Francisco', 'workplace');

    $this->user->syncCities(['San Francisco'], 'residence');

    $residenceCities = $this->user->cities()->wherePivot('group', 'residence')->get();
    $workplaceCities = $this->user->cities()->wherePivot('group', 'workplace')->get();

    expect($residenceCities)->toHaveCount(1)
        ->and($residenceCities->first()->name)->toBe('San Francisco')
        ->and($workplaceCities)->toHaveCount(1);
});

it('can detach city', function () {
    $this->user->attachCity('Los Angeles');
    $this->user->attachCity('San Francisco');

    $this->user->detachCity('Los Angeles');

    expect($this->user->cities)->toHaveCount(1)
        ->and($this->user->cities->first()->name)->toBe('San Francisco');
});

it('can detach all cities', function () {
    $this->user->attachCity('Los Angeles');
    $this->user->attachCity('San Francisco');

    $this->user->detachAllCities();

    expect($this->user->cities)->toHaveCount(0);
});

it('can detach all cities in specific group', function () {
    $this->user->attachCity('Los Angeles', 'residence');
    $this->user->attachCity('San Francisco', 'workplace');

    $this->user->detachAllCities('residence');

    expect($this->user->cities)->toHaveCount(1)
        ->and($this->user->cities->first()->name)->toBe('San Francisco');
});

it('chains city operations fluently', function () {
    $this->user
        ->attachCity('Los Angeles')
        ->attachCity('San Francisco');

    expect($this->user->cities)->toHaveCount(2);
});

it('can access city relationships', function () {
    $this->user->attachCity('Los Angeles');

    $city = $this->user->cities->first();

    expect($city->state->name)->toBe('California')
        ->and($city->state->country->name)->toBe('United States');
});
