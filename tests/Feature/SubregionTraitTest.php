<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Ritechoice23\Worldable\Models\Continent;
use Ritechoice23\Worldable\Models\Subregion;
use Ritechoice23\Worldable\Traits\HasSubregion;

class TestUserWithSubregion extends Model
{
    use HasSubregion;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function () {
    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
    });

    $this->user = TestUserWithSubregion::create([
        'name' => 'Test User',
    ]);

    $this->africa = Continent::create([
        'name' => 'Africa',
        'code' => 'AF',
    ]);

    $this->asia = Continent::create([
        'name' => 'Asia',
        'code' => 'AS',
    ]);

    $this->westernAfrica = Subregion::create([
        'name' => 'Western Africa',
        'code' => '011',
        'continent_id' => $this->africa->id,
    ]);

    $this->easternAfrica = Subregion::create([
        'name' => 'Eastern Africa',
        'code' => '014',
        'continent_id' => $this->africa->id,
    ]);

    $this->easternAsia = Subregion::create([
        'name' => 'Eastern Asia',
        'code' => '030',
        'continent_id' => $this->asia->id,
    ]);
});

afterEach(function () {
    Schema::dropIfExists('users');
});

it('can attach a subregion', function () {
    $this->user->attachSubregion('Western Africa');

    expect($this->user->subregions)->toHaveCount(1)
        ->and($this->user->subregions->first()->name)->toBe('Western Africa');
});

it('can attach subregion by code', function () {
    $this->user->attachSubregion('011');

    expect($this->user->subregions->first()->code)->toBe('011');
});

it('can attach subregion by model instance', function () {
    $this->user->attachSubregion($this->westernAfrica);

    expect($this->user->subregions->first()->id)->toBe($this->westernAfrica->id);
});

it('can attach subregion by ID', function () {
    $this->user->attachSubregion($this->westernAfrica->id);

    expect($this->user->subregions->first()->id)->toBe($this->westernAfrica->id);
});

it('can check if user has subregion', function () {
    $this->user->attachSubregion('Western Africa');

    expect($this->user->hasSubregion('Western Africa'))->toBeTrue()
        ->and($this->user->hasSubregion('Eastern Africa'))->toBeFalse();
});

it('can check subregion by code', function () {
    $this->user->attachSubregion('Western Africa');

    expect($this->user->hasSubregion('011'))->toBeTrue();
});

it('can attach multiple subregions at once', function () {
    $this->user->attachSubregions(['Western Africa', 'Eastern Africa']);

    expect($this->user->subregions)->toHaveCount(2);
});

it('can attach subregions with bulk operation', function () {
    $this->user->attachSubregions(['011', '014'], 'operations');

    $operations = $this->user->subregions()->wherePivot('group', 'operations')->get();

    expect($operations)->toHaveCount(2);
});

it('can filter users by subregion using whereInSubregion', function () {
    $user2 = TestUserWithSubregion::create(['name' => 'User 2']);

    $this->user->attachSubregion('Western Africa');
    $user2->attachSubregion('Eastern Asia');

    $westernAfricanUsers = TestUserWithSubregion::whereInSubregion('Western Africa')->get();

    expect($westernAfricanUsers)->toHaveCount(1)
        ->and($westernAfricanUsers->first()->name)->toBe('Test User');
});

it('can filter users excluding subregion using whereNotInSubregion', function () {
    $user2 = TestUserWithSubregion::create(['name' => 'User 2']);

    $this->user->attachSubregion('Western Africa');
    $user2->attachSubregion('Eastern Asia');

    $nonWesternAfricanUsers = TestUserWithSubregion::whereNotInSubregion('Western Africa')->get();

    expect($nonWesternAfricanUsers)->toHaveCount(1)
        ->and($nonWesternAfricanUsers->first()->name)->toBe('User 2');
});

it('can handle multiple subregions with groups', function () {
    $this->user->attachSubregion('Western Africa', 'market');
    $this->user->attachSubregion('Eastern Asia', 'operations');

    $marketSubregion = $this->user->subregions()->wherePivot('group', 'market')->first();
    $operationsSubregion = $this->user->subregions()->wherePivot('group', 'operations')->first();

    expect($marketSubregion->name)->toBe('Western Africa')
        ->and($operationsSubregion->name)->toBe('Eastern Asia');
});

it('can check subregion in specific group', function () {
    $this->user->attachSubregion('Western Africa', 'market');
    $this->user->attachSubregion('Eastern Asia', 'operations');

    expect($this->user->hasSubregion('Western Africa', 'market'))->toBeTrue()
        ->and($this->user->hasSubregion('Western Africa', 'operations'))->toBeFalse();
});

it('can filter by subregion and group', function () {
    $user2 = TestUserWithSubregion::create(['name' => 'User 2']);

    $this->user->attachSubregion('Western Africa', 'market');
    $user2->attachSubregion('Western Africa', 'operations');

    $marketUsers = TestUserWithSubregion::whereInSubregion('Western Africa', 'market')->get();

    expect($marketUsers)->toHaveCount(1)
        ->and($marketUsers->first()->name)->toBe('Test User');
});

it('can access subregion continent', function () {
    $this->user->attachSubregion('Western Africa');

    $subregion = $this->user->subregions->first();

    expect($subregion->continent->name)->toBe('Africa');
});

it('can sync subregions', function () {
    $this->user->attachSubregion('Western Africa');
    $this->user->attachSubregion('Eastern Africa');

    $this->user->syncSubregions(['Western Africa']);

    expect($this->user->subregions)->toHaveCount(1)
        ->and($this->user->subregions->first()->name)->toBe('Western Africa');
});

it('can sync subregions for specific group', function () {
    $this->user->attachSubregion('Western Africa', 'market');
    $this->user->attachSubregion('Eastern Asia', 'operations');

    $this->user->syncSubregions(['Eastern Africa'], 'market');

    $marketSubregions = $this->user->subregions()->wherePivot('group', 'market')->get();
    $operationsSubregions = $this->user->subregions()->wherePivot('group', 'operations')->get();

    expect($marketSubregions)->toHaveCount(1)
        ->and($marketSubregions->first()->name)->toBe('Eastern Africa')
        ->and($operationsSubregions)->toHaveCount(1);
});

it('can detach subregion', function () {
    $this->user->attachSubregion('Western Africa');
    $this->user->attachSubregion('Eastern Africa');

    $this->user->detachSubregion('Western Africa');

    expect($this->user->subregions)->toHaveCount(1)
        ->and($this->user->subregions->first()->name)->toBe('Eastern Africa');
});

it('can detach all subregions', function () {
    $this->user->attachSubregion('Western Africa');
    $this->user->attachSubregion('Eastern Africa');

    $this->user->detachAllSubregions();

    expect($this->user->subregions)->toHaveCount(0);
});

it('can detach all subregions in specific group', function () {
    $this->user->attachSubregion('Western Africa', 'market');
    $this->user->attachSubregion('Eastern Asia', 'operations');

    $this->user->detachAllSubregions('market');

    expect($this->user->subregions)->toHaveCount(1)
        ->and($this->user->subregions->first()->name)->toBe('Eastern Asia');
});

it('chains subregion operations fluently', function () {
    $this->user
        ->attachSubregion('Western Africa')
        ->attachSubregion('Eastern Asia');

    expect($this->user->subregions)->toHaveCount(2);
});

it('can find subregion by continent', function () {
    $africanSubregions = Subregion::ofContinent('Africa')->get();

    expect($africanSubregions)->toHaveCount(2)
        ->and($africanSubregions->pluck('name')->toArray())
        ->toContain('Western Africa')
        ->toContain('Eastern Africa');
});

it('can find subregion by continent code', function () {
    $africanSubregions = Subregion::ofContinent('AF')->get();

    expect($africanSubregions)->toHaveCount(2);
});

it('can access subregion name accessor', function () {
    $this->user->attachSubregion('Western Africa');

    expect($this->user->subregion_name)->toBe('Western Africa');
});

it('can access subregion code accessor', function () {
    $this->user->attachSubregion('Western Africa');

    expect($this->user->subregion_code)->toBe('011');
});
