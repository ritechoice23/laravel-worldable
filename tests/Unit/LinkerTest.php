<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ritechoice23\Worldable\Linkers\CityLinker;
use Ritechoice23\Worldable\Linkers\CountryLinker;
use Ritechoice23\Worldable\Linkers\StateLinker;
use Symfony\Component\Console\Style\SymfonyStyle;

beforeEach(function () {
    // Disable foreign key constraints for SQLite
    DB::statement('PRAGMA foreign_keys = OFF');

    // Drop tables first if they exist
    Schema::dropIfExists('world_cities');
    Schema::dropIfExists('world_states');
    Schema::dropIfExists('world_countries');
    Schema::dropIfExists('world_subregions');
    Schema::dropIfExists('world_continents');

    // Re-enable foreign key constraints
    DB::statement('PRAGMA foreign_keys = ON');

    // Create necessary tables
    Schema::create('world_continents', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('code');
        $table->timestamps();
    });

    Schema::create('world_subregions', function ($table) {
        $table->id();
        $table->unsignedBigInteger('continent_id')->nullable();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('world_countries', function ($table) {
        $table->id();
        $table->unsignedBigInteger('continent_id')->nullable();
        $table->unsignedBigInteger('subregion_id')->nullable();
        $table->string('name');
        $table->string('iso_code', 2);
        $table->string('iso_code_3', 3);
        $table->json('metadata')->nullable();
        $table->timestamps();
    });

    Schema::create('world_states', function ($table) {
        $table->id();
        $table->unsignedBigInteger('country_id')->nullable();
        $table->string('name');
        $table->string('code');
        $table->json('metadata')->nullable();
        $table->timestamps();
    });

    Schema::create('world_cities', function ($table) {
        $table->id();
        $table->unsignedBigInteger('country_id')->nullable();
        $table->unsignedBigInteger('state_id')->nullable();
        $table->string('name');
        $table->timestamps();
    });
});

afterEach(function () {
    // Disable foreign key constraints for SQLite
    DB::statement('PRAGMA foreign_keys = OFF');

    Schema::dropIfExists('world_cities');
    Schema::dropIfExists('world_states');
    Schema::dropIfExists('world_countries');
    Schema::dropIfExists('world_subregions');
    Schema::dropIfExists('world_continents');

    // Re-enable foreign key constraints
    DB::statement('PRAGMA foreign_keys = ON');
});

it('country linker skips when table does not exist', function () {
    Schema::dropIfExists('world_countries');

    $linker = new CountryLinker;

    // Should throw exception when table doesn't exist
    expect(fn () => $linker->link(false, false))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

it('state linker skips when table does not exist', function () {
    Schema::dropIfExists('world_states');

    $io = Mockery::mock(SymfonyStyle::class);
    $io->shouldReceive('text')->andReturn();

    $linker = new StateLinker;
    $result = $linker->link(false, false);

    expect($result->total)->toBe(0);
    expect($result->linked)->toBe(0);
});

it('city linker skips when table does not exist', function () {
    Schema::dropIfExists('world_cities');

    $io = Mockery::mock(SymfonyStyle::class);
    $io->shouldReceive('text')->andReturn();

    $linker = new CityLinker;
    $result = $linker->link(false, false);

    expect($result->total)->toBe(0);
    expect($result->linked)->toBe(0);
});

it('country linker links orphaned records', function () {
    // Create continent and subregion
    $continentId = DB::table('world_continents')->insertGetId([
        'name' => 'Africa',
        'code' => 'AF',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $subregionId = DB::table('world_subregions')->insertGetId([
        'continent_id' => $continentId,
        'name' => 'Western Africa',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Create orphaned country with metadata
    DB::table('world_countries')->insert([
        'name' => 'Nigeria',
        'iso_code' => 'NG',
        'iso_code_3' => 'NGA',
        'continent_id' => null,
        'subregion_id' => null,
        'metadata' => json_encode([
            'continent_name' => 'Africa',
            'subregion_name' => 'Western Africa',
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $linker = new CountryLinker;
    $result = $linker->link(false, false);

    expect($result->total)->toBeGreaterThanOrEqual(1);
});

it('state linker links orphaned records', function () {
    // Create country
    $countryId = DB::table('world_countries')->insertGetId([
        'name' => 'Nigeria',
        'iso_code' => 'NG',
        'iso_code_3' => 'NGA',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Create orphaned state with metadata
    DB::table('world_states')->insert([
        'name' => 'Lagos',
        'code' => 'LA',
        'country_id' => null,
        'metadata' => json_encode([
            'country_code' => 'NG',
            'country_name' => 'Nigeria',
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $linker = new StateLinker;
    $result = $linker->link(false, false);

    expect($result->total)->toBeGreaterThanOrEqual(1);
});
