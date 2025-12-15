<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Ritechoice23\Worldable\Models\Language;
use Ritechoice23\Worldable\Traits\HasLanguage;

class TestUserWithLanguage extends Model
{
    use HasLanguage;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function () {
    Schema::create('users', function ($table) {
        $table->id();
        $table->string('name');
    });

    $this->user = TestUserWithLanguage::create([
        'name' => 'Test User',
    ]);

    $this->english = Language::create([
        'name' => 'English',
        'native_name' => 'English',
        'iso_code' => 'en',
    ]);

    $this->spanish = Language::create([
        'name' => 'Spanish',
        'native_name' => 'Español',
        'iso_code' => 'es',
    ]);

    $this->french = Language::create([
        'name' => 'French',
        'native_name' => 'Français',
        'iso_code' => 'fr',
    ]);

    $this->german = Language::create([
        'name' => 'German',
        'native_name' => 'Deutsch',
        'iso_code' => 'de',
    ]);
});

afterEach(function () {
    Schema::dropIfExists('users');
});

it('can attach a language', function () {
    $this->user->attachLanguage('English');

    expect($this->user->languages)->toHaveCount(1)
        ->and($this->user->languages->first()->name)->toBe('English');
});

it('can attach language by ISO code', function () {
    $this->user->attachLanguage('en');

    expect($this->user->languages->first()->iso_code)->toBe('en');
});

it('can attach language by model instance', function () {
    $this->user->attachLanguage($this->english);

    expect($this->user->languages->first()->id)->toBe($this->english->id);
});

it('can attach language by ID', function () {
    $this->user->attachLanguage($this->english->id);

    expect($this->user->languages->first()->id)->toBe($this->english->id);
});

it('can check if user has language', function () {
    $this->user->attachLanguage('English');

    expect($this->user->hasLanguage('English'))->toBeTrue()
        ->and($this->user->hasLanguage('Spanish'))->toBeFalse();
});

it('can check language by ISO code', function () {
    $this->user->attachLanguage('English');

    expect($this->user->hasLanguage('en'))->toBeTrue();
});

it('can attach multiple languages at once', function () {
    $this->user->attachLanguages(['English', 'Spanish', 'French']);

    expect($this->user->languages)->toHaveCount(3);
});

it('can attach languages with bulk operation', function () {
    $this->user->attachLanguages(['en', 'es'], 'spoken');

    $spoken = $this->user->languages()->wherePivot('group', 'spoken')->get();

    expect($spoken)->toHaveCount(2);
});

it('can filter users by language using whereSpeaks', function () {
    $user2 = TestUserWithLanguage::create(['name' => 'User 2']);

    $this->user->attachLanguage('English');
    $user2->attachLanguage('Spanish');

    $englishSpeakers = TestUserWithLanguage::whereSpeaks('English')->get();

    expect($englishSpeakers)->toHaveCount(1)
        ->and($englishSpeakers->first()->name)->toBe('Test User');
});

it('can filter by language ISO code', function () {
    $this->user->attachLanguage('English');

    $users = TestUserWithLanguage::whereSpeaks('en')->get();

    expect($users)->toHaveCount(1)
        ->and($users->first()->name)->toBe('Test User');
});

it('can filter users excluding language using whereNotSpeaks', function () {
    $user2 = TestUserWithLanguage::create(['name' => 'User 2']);

    $this->user->attachLanguage('English');
    $user2->attachLanguage('Spanish');

    $nonEnglishSpeakers = TestUserWithLanguage::whereNotSpeaks('English')->get();

    expect($nonEnglishSpeakers)->toHaveCount(1)
        ->and($nonEnglishSpeakers->first()->name)->toBe('User 2');
});

it('can handle multiple languages with groups', function () {
    $this->user->attachLanguage('English', 'native');
    $this->user->attachLanguage('Spanish', 'learning');

    $nativeLanguage = $this->user->languages()->wherePivot('group', 'native')->first();
    $learningLanguage = $this->user->languages()->wherePivot('group', 'learning')->first();

    expect($nativeLanguage->name)->toBe('English')
        ->and($learningLanguage->name)->toBe('Spanish');
});

it('can check language in specific group', function () {
    $this->user->attachLanguage('English', 'native');
    $this->user->attachLanguage('Spanish', 'learning');

    expect($this->user->hasLanguage('English', 'native'))->toBeTrue()
        ->and($this->user->hasLanguage('English', 'learning'))->toBeFalse();
});

it('can filter by language and group', function () {
    $user2 = TestUserWithLanguage::create(['name' => 'User 2']);

    $this->user->attachLanguage('English', 'native');
    $user2->attachLanguage('English', 'learning');

    $nativeSpeakers = TestUserWithLanguage::whereSpeaks('English', 'native')->get();

    expect($nativeSpeakers)->toHaveCount(1)
        ->and($nativeSpeakers->first()->name)->toBe('Test User');
});

it('can access native name', function () {
    $this->user->attachLanguage('Spanish');

    $language = $this->user->languages->first();

    expect($language->native_name)->toBe('Español');
});

it('can sync languages', function () {
    $this->user->attachLanguage('English');
    $this->user->attachLanguage('Spanish');

    $this->user->syncLanguages(['English']);

    expect($this->user->languages)->toHaveCount(1)
        ->and($this->user->languages->first()->name)->toBe('English');
});

it('can sync languages for specific group', function () {
    $this->user->attachLanguage('English', 'native');
    $this->user->attachLanguage('Spanish', 'learning');

    $this->user->syncLanguages(['French'], 'learning');

    $nativeLanguages = $this->user->languages()->wherePivot('group', 'native')->get();
    $learningLanguages = $this->user->languages()->wherePivot('group', 'learning')->get();

    expect($nativeLanguages)->toHaveCount(1)
        ->and($learningLanguages)->toHaveCount(1)
        ->and($learningLanguages->first()->name)->toBe('French');
});

it('can detach language', function () {
    $this->user->attachLanguage('English');
    $this->user->attachLanguage('Spanish');

    $this->user->detachLanguage('English');

    expect($this->user->languages)->toHaveCount(1)
        ->and($this->user->languages->first()->name)->toBe('Spanish');
});

it('can detach all languages', function () {
    $this->user->attachLanguage('English');
    $this->user->attachLanguage('Spanish');

    $this->user->detachAllLanguages();

    expect($this->user->languages)->toHaveCount(0);
});

it('can detach all languages in specific group', function () {
    $this->user->attachLanguage('English', 'native');
    $this->user->attachLanguage('Spanish', 'learning');

    $this->user->detachAllLanguages('learning');

    expect($this->user->languages)->toHaveCount(1)
        ->and($this->user->languages->first()->name)->toBe('English');
});

it('chains language operations fluently', function () {
    $this->user
        ->attachLanguage('English')
        ->attachLanguage('Spanish');

    expect($this->user->languages)->toHaveCount(2);
});

it('handles multilingual context', function () {
    $this->user->attachLanguages(['English', 'Spanish'], 'spoken');
    $this->user->attachLanguages(['French', 'German'], 'learning');

    $spokenCount = $this->user->languages()->wherePivot('group', 'spoken')->count();
    $learningCount = $this->user->languages()->wherePivot('group', 'learning')->count();

    expect($spokenCount)->toBe(2)
        ->and($learningCount)->toBe(2);
});
