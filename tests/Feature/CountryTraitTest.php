<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Ritechoice23\Worldable\Models\Country;
use Ritechoice23\Worldable\Traits\HasCountry;

beforeEach(function () {
    // Clean up existing data
    DB::table('worldables')->truncate();
    DB::table('world_countries')->truncate();

    // Create test model
    $this->userClass = new class extends Model
    {
        use HasCountry;

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

    // Create test countries
    Country::create([
        'name' => 'Nigeria',
        'iso_code' => 'NG',
        'iso_code_3' => 'NGA',
        'calling_code' => '+234',
    ]);

    Country::create([
        'name' => 'United States',
        'iso_code' => 'US',
        'iso_code_3' => 'USA',
        'calling_code' => '+1',
    ]);
});

afterEach(function () {
    DB::table('worldables')->truncate();
    DB::table('world_countries')->truncate();
    DB::table('users')->truncate();
});

it('can attach a country by name', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachCountry('Nigeria');

    expect($user->countries()->count())->toBe(1);
    expect($user->countries->first()->name)->toBe('Nigeria');
});

it('can attach country by ISO code', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachCountry('NG');

    expect($user->countries()->count())->toBe(1);
    expect($user->countries->first()->iso_code)->toBe('NG');
});

it('can attach country by ISO 3 code', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachCountry('NGA');

    expect($user->countries()->count())->toBe(1);
    expect($user->countries->first()->iso_code_3)->toBe('NGA');
});

it('can attach country by model instance', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $country = Country::where('name', 'Nigeria')->first();

    $user->attachCountry($country);

    expect($user->countries()->count())->toBe(1);
    expect($user->countries->first()->id)->toBe($country->id);
});

it('can attach country by ID', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $country = Country::where('name', 'Nigeria')->first();

    $user->attachCountry($country->id);

    expect($user->countries()->count())->toBe(1);
    expect($user->countries->first()->id)->toBe($country->id);
});

it('can check if user has country', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachCountry('Nigeria');

    expect($user->hasCountry('Nigeria'))->toBeTrue();
    expect($user->hasCountry('United States'))->toBeFalse();
});

it('can check country by ISO code', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachCountry('Nigeria');

    expect($user->hasCountry('NG'))->toBeTrue();
    expect($user->hasCountry('US'))->toBeFalse();
});

it('can attach multiple countries at once', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachCountries(['Nigeria', 'United States']);

    expect($user->countries()->count())->toBe(2);
});

it('can attach countries with bulk operation', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachCountries(['NG', 'US']);

    expect($user->countries()->count())->toBe(2);
});

it('can filter users by country using whereFrom', function () {
    $user1 = $this->userClass::create(['name' => 'John Doe']);
    $user2 = $this->userClass::create(['name' => 'Jane Doe']);

    $user1->attachCountry('Nigeria');
    $user2->attachCountry('United States');

    $nigerianUsers = $this->userClass::whereFrom('Nigeria')->get();

    expect($nigerianUsers->count())->toBe(1);
    expect($nigerianUsers->first()->id)->toBe($user1->id);
});

it('can filter by country ISO code', function () {
    $user1 = $this->userClass::create(['name' => 'John Doe']);
    $user2 = $this->userClass::create(['name' => 'Jane Doe']);

    $user1->attachCountry('Nigeria');
    $user2->attachCountry('United States');

    $nigerianUsers = $this->userClass::whereFrom('NG')->get();

    expect($nigerianUsers->count())->toBe(1);
    expect($nigerianUsers->first()->id)->toBe($user1->id);
});

it('can filter users excluding country using whereNotFrom', function () {
    $user1 = $this->userClass::create(['name' => 'John Doe']);
    $user2 = $this->userClass::create(['name' => 'Jane Doe']);

    $user1->attachCountry('Nigeria');
    $user2->attachCountry('United States');

    $nonNigerianUsers = $this->userClass::whereNotFrom('Nigeria')->get();

    expect($nonNigerianUsers->count())->toBe(1);
    expect($nonNigerianUsers->first()->id)->toBe($user2->id);
});

it('can handle multiple countries with groups', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachCountry('Nigeria', 'citizenship');
    $user->attachCountry('United States', 'residence');

    expect($user->countries()->count())->toBe(2);
    expect($user->countries()->wherePivot('group', 'citizenship')->first()->name)->toBe('Nigeria');
    expect($user->countries()->wherePivot('group', 'residence')->first()->name)->toBe('United States');
});

it('can check country in specific group', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachCountry('Nigeria', 'citizenship');
    $user->attachCountry('United States', 'residence');

    expect($user->hasCountry('Nigeria', 'citizenship'))->toBeTrue();
    expect($user->hasCountry('Nigeria', 'residence'))->toBeFalse();
});

it('can filter by country and group', function () {
    $user1 = $this->userClass::create(['name' => 'John Doe']);
    $user2 = $this->userClass::create(['name' => 'Jane Doe']);

    $user1->attachCountry('Nigeria', 'citizenship');
    $user2->attachCountry('Nigeria', 'residence');

    $citizenshipUsers = $this->userClass::whereFrom('Nigeria', 'citizenship')->get();

    expect($citizenshipUsers->count())->toBe(1);
    expect($citizenshipUsers->first()->id)->toBe($user1->id);
});

it('can access country name accessor', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachCountry('Nigeria');

    expect($user->country_name)->toBe('Nigeria');
});

it('can access country code accessor', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachCountry('Nigeria');

    expect($user->country_code)->toBe('NG');
});

it('can sync countries', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachCountries(['Nigeria', 'United States']);
    expect($user->countries()->count())->toBe(2);

    $user->syncCountries(['Nigeria']);
    expect($user->countries()->count())->toBe(1);
    expect($user->countries->first()->name)->toBe('Nigeria');
});

it('can sync countries for specific group', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachCountry('Nigeria', 'citizenship');
    $user->attachCountry('United States', 'residence');

    $user->syncCountries(['Nigeria'], 'residence');

    expect($user->countries()->wherePivot('group', 'citizenship')->count())->toBe(1);
    expect($user->countries()->wherePivot('group', 'residence')->count())->toBe(1);
    expect($user->countries()->wherePivot('group', 'residence')->first()->name)->toBe('Nigeria');
});

it('can detach country', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachCountry('Nigeria');

    $user->detachCountry('Nigeria');

    expect($user->countries()->count())->toBe(0);
});

it('can detach all countries', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);
    $user->attachCountries(['Nigeria', 'United States']);

    $user->detachAllCountries();

    expect($user->countries()->count())->toBe(0);
});

it('can detach all countries in specific group', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $user->attachCountry('Nigeria', 'citizenship');
    $user->attachCountry('United States', 'residence');

    $user->detachAllCountries('citizenship');

    expect($user->countries()->wherePivot('group', 'citizenship')->count())->toBe(0);
    expect($user->countries()->wherePivot('group', 'residence')->count())->toBe(1);
});

it('chains country operations fluently', function () {
    $user = $this->userClass::create(['name' => 'John Doe']);

    $result = $user
        ->attachCountry('Nigeria')
        ->attachCountry('United States');

    expect($result)->toBeInstanceOf($this->userClass::class);
    expect($user->countries()->count())->toBe(2);
});
