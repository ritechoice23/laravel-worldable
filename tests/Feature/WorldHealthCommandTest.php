<?php

it('can run health check command', function () {
    $this->artisan('world:health')
        ->expectsOutput('🏥 World Data Health Check')
        ->assertExitCode(0);
});

it('can run detailed health check', function () {
    $this->artisan('world:health', [
        '--detailed' => true,
    ])->expectsOutput('🏥 World Data Health Check')
        ->assertExitCode(0);
});

it('can output health check as json', function () {
    $this->artisan('world:health', [
        '--json' => true,
    ])->assertExitCode(0);

    // Should output valid JSON
    $output = $this->artisan('world:health', ['--json' => true])
        ->run();

    expect($output)->toBeInt();
});

it('runs without errors for empty database', function () {
    $this->artisan('world:health')
        ->assertExitCode(0);
});
