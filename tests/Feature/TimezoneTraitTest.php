<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Ritechoice23\Worldable\Models\Timezone;
use Ritechoice23\Worldable\Traits\HasTimezone;

class TestUserWithTimezone extends Model
{
    use HasTimezone;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function () {
    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
    });

    $this->user = TestUserWithTimezone::create([
        'name' => 'Test User',
    ]);

    $this->eastern = Timezone::create([
        'name' => 'Eastern Standard Time',
        'zone_name' => 'America/New_York',
        'gmt_offset' => -18000,
        'gmt_offset_name' => 'UTC-05:00',
        'abbreviation' => 'EST',
    ]);

    $this->pacific = Timezone::create([
        'name' => 'Pacific Standard Time',
        'zone_name' => 'America/Los_Angeles',
        'gmt_offset' => -28800,
        'gmt_offset_name' => 'UTC-08:00',
        'abbreviation' => 'PST',
    ]);

    $this->tokyo = Timezone::create([
        'name' => 'Japan Standard Time',
        'zone_name' => 'Asia/Tokyo',
        'gmt_offset' => 32400,
        'gmt_offset_name' => 'UTC+09:00',
        'abbreviation' => 'JST',
    ]);
});

afterEach(function () {
    Schema::dropIfExists('users');
});

it('can attach a timezone', function () {
    $this->user->attachTimezone('America/New_York');

    expect($this->user->timezones)->toHaveCount(1)
        ->and($this->user->timezones->first()->zone_name)->toBe('America/New_York');
});

it('can attach timezone by abbreviation', function () {
    $this->user->attachTimezone('EST');

    expect($this->user->timezones->first()->abbreviation)->toBe('EST');
});

it('can attach timezone by model instance', function () {
    $this->user->attachTimezone($this->eastern);

    expect($this->user->timezones->first()->id)->toBe($this->eastern->id);
});

it('can attach timezone by ID', function () {
    $this->user->attachTimezone($this->eastern->id);

    expect($this->user->timezones->first()->id)->toBe($this->eastern->id);
});

it('can check if user has timezone', function () {
    $this->user->attachTimezone('America/New_York');

    expect($this->user->hasTimezone('America/New_York'))->toBeTrue()
        ->and($this->user->hasTimezone('Asia/Tokyo'))->toBeFalse();
});

it('can check timezone by abbreviation', function () {
    $this->user->attachTimezone('America/New_York');

    expect($this->user->hasTimezone('EST'))->toBeTrue();
});

it('can attach multiple timezones at once', function () {
    $this->user->attachTimezones(['America/New_York', 'America/Los_Angeles']);

    expect($this->user->timezones)->toHaveCount(2);
});

it('can attach timezones with bulk operation', function () {
    $this->user->attachTimezones(['EST', 'PST'], 'available');

    $available = $this->user->timezones()->wherePivot('group', 'available')->get();

    expect($available)->toHaveCount(2);
});

it('can filter users by timezone using whereInTimezone', function () {
    $user2 = TestUserWithTimezone::create(['name' => 'User 2']);

    $this->user->attachTimezone('America/New_York');
    $user2->attachTimezone('Asia/Tokyo');

    $easternUsers = TestUserWithTimezone::whereInTimezone('America/New_York')->get();

    expect($easternUsers)->toHaveCount(1)
        ->and($easternUsers->first()->name)->toBe('Test User');
});

it('can filter by timezone abbreviation', function () {
    $this->user->attachTimezone('America/New_York');

    $users = TestUserWithTimezone::whereInTimezone('EST')->get();

    expect($users)->toHaveCount(1)
        ->and($users->first()->name)->toBe('Test User');
});

it('can filter users excluding timezone using whereNotInTimezone', function () {
    $user2 = TestUserWithTimezone::create(['name' => 'User 2']);

    $this->user->attachTimezone('America/New_York');
    $user2->attachTimezone('Asia/Tokyo');

    $nonEasternUsers = TestUserWithTimezone::whereNotInTimezone('America/New_York')->get();

    expect($nonEasternUsers)->toHaveCount(1)
        ->and($nonEasternUsers->first()->name)->toBe('User 2');
});

it('can handle multiple timezones with groups', function () {
    $this->user->attachTimezone('America/New_York', 'residence');
    $this->user->attachTimezone('Asia/Tokyo', 'work');

    $residenceTz = $this->user->timezones()->wherePivot('group', 'residence')->first();
    $workTz = $this->user->timezones()->wherePivot('group', 'work')->first();

    expect($residenceTz->zone_name)->toBe('America/New_York')
        ->and($workTz->zone_name)->toBe('Asia/Tokyo');
});

it('can check timezone in specific group', function () {
    $this->user->attachTimezone('America/New_York', 'residence');
    $this->user->attachTimezone('Asia/Tokyo', 'work');

    expect($this->user->hasTimezone('America/New_York', 'residence'))->toBeTrue()
        ->and($this->user->hasTimezone('America/New_York', 'work'))->toBeFalse();
});

it('can filter by timezone and group', function () {
    $user2 = TestUserWithTimezone::create(['name' => 'User 2']);

    $this->user->attachTimezone('America/New_York', 'residence');
    $user2->attachTimezone('America/New_York', 'work');

    $residents = TestUserWithTimezone::whereInTimezone('America/New_York', 'residence')->get();

    expect($residents)->toHaveCount(1)
        ->and($residents->first()->name)->toBe('Test User');
});

it('can access timezone properties', function () {
    $this->user->attachTimezone('America/New_York');

    $timezone = $this->user->timezones->first();

    expect($timezone->name)->toBe('Eastern Standard Time')
        ->and($timezone->gmt_offset)->toBe(-18000)
        ->and($timezone->gmt_offset_name)->toBe('UTC-05:00')
        ->and($timezone->abbreviation)->toBe('EST');
});

it('can sync timezones', function () {
    $this->user->attachTimezone('America/New_York');
    $this->user->attachTimezone('America/Los_Angeles');

    $this->user->syncTimezones(['America/New_York']);

    expect($this->user->timezones)->toHaveCount(1)
        ->and($this->user->timezones->first()->zone_name)->toBe('America/New_York');
});

it('can sync timezones for specific group', function () {
    $this->user->attachTimezone('America/New_York', 'residence');
    $this->user->attachTimezone('Asia/Tokyo', 'work');

    $this->user->syncTimezones(['America/Los_Angeles'], 'residence');

    $residenceTzs = $this->user->timezones()->wherePivot('group', 'residence')->get();
    $workTzs = $this->user->timezones()->wherePivot('group', 'work')->get();

    expect($residenceTzs)->toHaveCount(1)
        ->and($residenceTzs->first()->zone_name)->toBe('America/Los_Angeles')
        ->and($workTzs)->toHaveCount(1);
});

it('can detach timezone', function () {
    $this->user->attachTimezone('America/New_York');
    $this->user->attachTimezone('America/Los_Angeles');

    $this->user->detachTimezone('America/New_York');

    expect($this->user->timezones)->toHaveCount(1)
        ->and($this->user->timezones->first()->zone_name)->toBe('America/Los_Angeles');
});

it('can detach all timezones', function () {
    $this->user->attachTimezone('America/New_York');
    $this->user->attachTimezone('America/Los_Angeles');

    $this->user->detachAllTimezones();

    expect($this->user->timezones)->toHaveCount(0);
});

it('can detach all timezones in specific group', function () {
    $this->user->attachTimezone('America/New_York', 'residence');
    $this->user->attachTimezone('Asia/Tokyo', 'work');

    $this->user->detachAllTimezones('residence');

    expect($this->user->timezones)->toHaveCount(1)
        ->and($this->user->timezones->first()->zone_name)->toBe('Asia/Tokyo');
});

it('chains timezone operations fluently', function () {
    $this->user
        ->attachTimezone('America/New_York')
        ->attachTimezone('Asia/Tokyo');

    expect($this->user->timezones)->toHaveCount(2);
});

it('handles different GMT offsets', function () {
    $this->user->attachTimezone('America/New_York');  // UTC-5
    $this->user->attachTimezone('Asia/Tokyo');        // UTC+9

    $timezones = $this->user->timezones;

    expect($timezones->first()->gmt_offset)->toBe(-18000)
        ->and($timezones->last()->gmt_offset)->toBe(32400);
});

it('can convert time to local timezone', function () {
    $this->user->attachTimezone('America/New_York');

    $localTime = $this->user->localTime();

    expect($localTime)->toBeInstanceOf(\Carbon\Carbon::class);
});
