<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Ritechoice23\Worldable\Models\Continent;
use Ritechoice23\Worldable\Traits\HasContinent;

beforeEach(function () {
    // Clean up existing data
    DB::table('worldables')->truncate();
    DB::table('world_continents')->truncate();

    // Create test model
    $this->userClass = new class extends Model
    {
        use HasContinent;

        protected $table = 'users';

        protected $guarded = [];

        public $timestamps = false;
    };

    // Create users table if it doesn't exist
    if (! \Illuminate\Support\Facades\Schema::hasTable('users')) {
        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    } else {
        DB::table('users')->truncate();
    }

    // Create test continents
    Continent::create(['name' => 'Africa', 'code' => 'AF']);
    Continent::create(['name' => 'Europe', 'code' => 'EU']);
    Continent::create(['name' => 'Asia', 'code' => 'AS']);
});

afterEach(function () {
    DB::table('worldables')->truncate();
    DB::table('world_continents')->truncate();
    DB::table('users')->truncate();
});

it('can attach a continent by name', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachContinent('Africa');

    expect($user->continents()->count())->toBe(1);
    expect($user->continents->first()->name)->toBe('Africa');
});

it('can attach continent by code', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachContinent('AF');

    expect($user->continents()->count())->toBe(1);
    expect($user->continents->first()->code)->toBe('AF');
});

it('can attach continent by model instance', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $continent = Continent::where('name', 'Africa')->first();

    $user->attachContinent($continent);

    expect($user->continents()->count())->toBe(1);
    expect($user->continents->first()->id)->toBe($continent->id);
});

it('can attach continent by ID', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $continent = Continent::where('name', 'Africa')->first();

    $user->attachContinent($continent->id);

    expect($user->continents()->count())->toBe(1);
    expect($user->continents->first()->id)->toBe($continent->id);
});

it('can check if user has continent', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachContinent('Africa');

    expect($user->hasContinent('Africa'))->toBeTrue();
    expect($user->hasContinent('Europe'))->toBeFalse();
});

it('can check continent by code', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachContinent('Africa');

    expect($user->hasContinent('AF'))->toBeTrue();
    expect($user->hasContinent('EU'))->toBeFalse();
});

it('can attach multiple continents at once', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachContinents(['Africa', 'Europe']);

    expect($user->continents()->count())->toBe(2);
});

it('can attach continents with bulk operation', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachContinents(['AF', 'EU']);

    expect($user->continents()->count())->toBe(2);
});

it('can filter users by continent using whereInContinent', function () {
    $user1 = $this->userClass::create(['name' => 'John Doe']);
    $user2 = $this->userClass::create(['name' => 'Jane Doe']);

    $user1->attachContinent('Africa');
    $user2->attachContinent('Europe');

    $africanUsers = $this->userClass::whereInContinent('Africa')->get();

    expect($africanUsers->count())->toBe(1);
    expect($africanUsers->first()->id)->toBe($user1->id);
});

it('can filter by continent code', function () {
    $user1 = $this->userClass::create(['name' => 'John Doe']);
    $user2 = $this->userClass::create(['name' => 'Jane Doe']);

    $user1->attachContinent('Africa');
    $user2->attachContinent('Europe');

    $africanUsers = $this->userClass::whereInContinent('AF')->get();

    expect($africanUsers->count())->toBe(1);
    expect($africanUsers->first()->id)->toBe($user1->id);
});

it('can filter users excluding continent using whereNotInContinent', function () {
    $user1 = $this->userClass::create(['name' => 'John Doe']);
    $user2 = $this->userClass::create(['name' => 'Jane Doe']);

    $user1->attachContinent('Africa');
    $user2->attachContinent('Europe');

    $nonAfricanUsers = $this->userClass::whereNotInContinent('Africa')->get();

    expect($nonAfricanUsers->count())->toBe(1);
    expect($nonAfricanUsers->first()->id)->toBe($user2->id);
});

it('can handle multiple continents with groups', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachContinent('Africa', 'birth');
    $user->attachContinent('Europe', 'residence');

    expect($user->continents()->count())->toBe(2);
    expect($user->continents()->wherePivot('group', 'birth')->first()->name)->toBe('Africa');
    expect($user->continents()->wherePivot('group', 'residence')->first()->name)->toBe('Europe');
});

it('can check continent in specific group', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachContinent('Africa', 'birth');
    $user->attachContinent('Europe', 'residence');

    expect($user->hasContinent('Africa', 'birth'))->toBeTrue();
    expect($user->hasContinent('Africa', 'residence'))->toBeFalse();
});

it('can filter by continent and group', function () {
    $user1 = $this->userClass::create(['name' => 'John Doe']);
    $user2 = $this->userClass::create(['name' => 'Jane Doe']);

    $user1->attachContinent('Africa', 'birth');
    $user2->attachContinent('Africa', 'residence');

    $birthUsers = $this->userClass::whereInContinent('Africa', 'birth')->get();

    expect($birthUsers->count())->toBe(1);
    expect($birthUsers->first()->id)->toBe($user1->id);
});

it('can access continent name accessor', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachContinent('Africa');

    expect($user->continent_name)->toBe('Africa');
});

it('can access continent code accessor', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachContinent('Africa');

    expect($user->continent_code)->toBe('AF');
});

it('can sync continents', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachContinents(['Africa', 'Europe']);
    expect($user->continents()->count())->toBe(2);

    $user->syncContinents(['Africa']);
    expect($user->continents()->count())->toBe(1);
    expect($user->continents->first()->name)->toBe('Africa');
});

it('can sync continents for specific group', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachContinent('Africa', 'birth');
    $user->attachContinent('Europe', 'residence');

    $user->syncContinents(['Asia'], 'residence');

    expect($user->continents()->wherePivot('group', 'birth')->count())->toBe(1);
    expect($user->continents()->wherePivot('group', 'residence')->count())->toBe(1);
    expect($user->continents()->wherePivot('group', 'residence')->first()->name)->toBe('Asia');
});

it('can detach continent', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachContinent('Africa');

    $user->detachContinent('Africa');

    expect($user->continents()->count())->toBe(0);
});

it('can detach all continents', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachContinents(['Africa', 'Europe']);

    $user->detachAllContinents();

    expect($user->continents()->count())->toBe(0);
});

it('can detach all continents in specific group', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachContinent('Africa', 'birth');
    $user->attachContinent('Europe', 'residence');

    $user->detachAllContinents('birth');

    expect($user->continents()->wherePivot('group', 'birth')->count())->toBe(0);
    expect($user->continents()->wherePivot('group', 'residence')->count())->toBe(1);
});

it('chains continent operations fluently', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $result = $user
        ->attachContinent('Africa')
        ->attachContinent('Europe');

    expect($result)->toBeInstanceOf($this->userClass::class);
    expect($user->continents()->count())->toBe(2);
});
