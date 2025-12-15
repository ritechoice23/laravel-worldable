<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Ritechoice23\Worldable\Models\Country;
use Ritechoice23\Worldable\Models\State;
use Ritechoice23\Worldable\Traits\HasState;

class TestUserWithState extends Model
{
    use HasState;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function () {
    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
    });

    $this->user = TestUserWithState::create([
        'name' => 'Test User',
    ]);

    $this->country = Country::create([
        'name' => 'United States',
        'iso_code' => 'US',
        'iso_code_3' => 'USA',
        'continent_id' => 1,
    ]);

    $this->california = State::create([
        'name' => 'California',
        'code' => 'CA',
        'country_id' => $this->country->id,
    ]);

    $this->texas = State::create([
        'name' => 'Texas',
        'code' => 'TX',
        'country_id' => $this->country->id,
    ]);
});

afterEach(function () {
    Schema::dropIfExists('users');
});

it('can attach a state', function () {
    $this->user->attachState('California');

    expect($this->user->states)->toHaveCount(1)
        ->and($this->user->states->first()->name)->toBe('California');
});

it('can attach state by code', function () {
    $this->user->attachState('CA');

    expect($this->user->states->first()->code)->toBe('CA');
});

it('can attach state by model instance', function () {
    $this->user->attachState($this->california);

    expect($this->user->states->first()->id)->toBe($this->california->id);
});

it('can attach state by ID', function () {
    $this->user->attachState($this->california->id);

    expect($this->user->states->first()->id)->toBe($this->california->id);
});

it('can check if user has state', function () {
    $this->user->attachState('California');

    expect($this->user->hasState('California'))->toBeTrue()
        ->and($this->user->hasState('Texas'))->toBeFalse();
});

it('can check state by code', function () {
    $this->user->attachState('California');

    expect($this->user->hasState('CA'))->toBeTrue();
});

it('can attach multiple states at once', function () {
    $this->user->attachStates(['California', 'Texas']);

    expect($this->user->states)->toHaveCount(2);
});

it('can attach states with bulk operation', function () {
    $this->user->attachStates(['CA', 'TX'], 'visited');

    $visited = $this->user->states()->wherePivot('group', 'visited')->get();

    expect($visited)->toHaveCount(2);
});

it('can filter users by state using whereInState', function () {
    $user2 = TestUserWithState::create(['name' => 'User 2']);

    $this->user->attachState('California');
    $user2->attachState('Texas');

    $californiaUsers = TestUserWithState::whereInState('California')->get();

    expect($californiaUsers)->toHaveCount(1)
        ->and($californiaUsers->first()->name)->toBe('Test User');
});

it('can filter users excluding state using whereNotInState', function () {
    $user2 = TestUserWithState::create(['name' => 'User 2']);

    $this->user->attachState('California');
    $user2->attachState('Texas');

    $nonCaliforniaUsers = TestUserWithState::whereNotInState('California')->get();

    expect($nonCaliforniaUsers)->toHaveCount(1)
        ->and($nonCaliforniaUsers->first()->name)->toBe('User 2');
});

it('can handle multiple states with groups', function () {
    $this->user->attachState('California', 'residence');
    $this->user->attachState('Texas', 'workplace');

    $residenceState = $this->user->states()->wherePivot('group', 'residence')->first();
    $workplaceState = $this->user->states()->wherePivot('group', 'workplace')->first();

    expect($residenceState->name)->toBe('California')
        ->and($workplaceState->name)->toBe('Texas');
});

it('can check state in specific group', function () {
    $this->user->attachState('California', 'residence');
    $this->user->attachState('Texas', 'workplace');

    expect($this->user->hasState('California', 'residence'))->toBeTrue()
        ->and($this->user->hasState('California', 'workplace'))->toBeFalse();
});

it('can filter by state and group', function () {
    $user2 = TestUserWithState::create(['name' => 'User 2']);

    $this->user->attachState('California', 'residence');
    $user2->attachState('California', 'workplace');

    $residents = TestUserWithState::whereInState('California', 'residence')->get();

    expect($residents)->toHaveCount(1)
        ->and($residents->first()->name)->toBe('Test User');
});

it('can sync states', function () {
    $this->user->attachState('California');
    $this->user->attachState('Texas');

    $this->user->syncStates(['California']);

    expect($this->user->states)->toHaveCount(1)
        ->and($this->user->states->first()->name)->toBe('California');
});

it('can sync states for specific group', function () {
    $this->user->attachState('California', 'residence');
    $this->user->attachState('Texas', 'workplace');

    $this->user->syncStates(['Texas'], 'residence');

    $residenceStates = $this->user->states()->wherePivot('group', 'residence')->get();
    $workplaceStates = $this->user->states()->wherePivot('group', 'workplace')->get();

    expect($residenceStates)->toHaveCount(1)
        ->and($residenceStates->first()->name)->toBe('Texas')
        ->and($workplaceStates)->toHaveCount(1);
});

it('can detach state', function () {
    $this->user->attachState('California');
    $this->user->attachState('Texas');

    $this->user->detachState('California');

    expect($this->user->states)->toHaveCount(1)
        ->and($this->user->states->first()->name)->toBe('Texas');
});

it('can detach all states', function () {
    $this->user->attachState('California');
    $this->user->attachState('Texas');

    $this->user->detachAllStates();

    expect($this->user->states)->toHaveCount(0);
});

it('can detach all states in specific group', function () {
    $this->user->attachState('California', 'residence');
    $this->user->attachState('Texas', 'workplace');

    $this->user->detachAllStates('residence');

    expect($this->user->states)->toHaveCount(1)
        ->and($this->user->states->first()->name)->toBe('Texas');
});

it('chains state operations fluently', function () {
    $this->user
        ->attachState('California')
        ->attachState('Texas');

    expect($this->user->states)->toHaveCount(2);
});
