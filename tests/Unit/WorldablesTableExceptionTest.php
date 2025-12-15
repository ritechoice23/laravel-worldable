<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Ritechoice23\Worldable\Traits\HasCity;
use Ritechoice23\Worldable\Traits\HasCountry;
use Ritechoice23\Worldable\Traits\HasCurrency;

beforeEach(function () {
    // Drop worldables table if exists
    Schema::dropIfExists('worldables');

    // Create test model
    $this->testModel = new class extends Model
    {
        use HasCity, HasCountry, HasCurrency;

        protected $table = 'test_users';

        protected $guarded = [];
    };

    // Create test table
    if (! Schema::hasTable('test_users')) {
        Schema::create('test_users', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }
});

afterEach(function () {
    Schema::dropIfExists('test_users');
    Schema::dropIfExists('worldables');
});

it('throws exception when accessing countries without worldables table', function () {
    $user = $this->testModel::create(['name' => 'Test User']);

    expect(fn () => $user->countries())
        ->toThrow(RuntimeException::class, 'worldables');
});

it('throws exception when accessing cities without worldables table', function () {
    $user = $this->testModel::create(['name' => 'Test User']);

    expect(fn () => $user->cities())
        ->toThrow(RuntimeException::class, 'worldables');
});

it('throws exception when accessing currencies without worldables table', function () {
    $user = $this->testModel::create(['name' => 'Test User']);

    expect(fn () => $user->currencies())
        ->toThrow(RuntimeException::class, 'worldables');
});

it('provides helpful error message with install command', function () {
    $user = $this->testModel::create(['name' => 'Test User']);

    try {
        $user->countries();
        expect(false)->toBeTrue(); // Should not reach here
    } catch (RuntimeException $e) {
        expect($e->getMessage())
            ->toContain('php artisan world:install --worldables');
    }
});

it('works correctly when worldables table exists', function () {
    // Create worldables table
    Schema::create('worldables', function ($table) {
        $table->id();
        $table->morphs('worldable');
        $table->unsignedBigInteger('world_entity_id');
        $table->string('world_entity_type');
        $table->string('group')->nullable();
        $table->json('meta')->nullable();
        $table->timestamps();
    });

    $user = $this->testModel::create(['name' => 'Test User']);

    // Should not throw exception
    $countries = $user->countries();

    expect($countries)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class);
});
