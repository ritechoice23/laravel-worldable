<?php

use Ritechoice23\Worldable\Commands\WorldInstallCommand;

it('can run world install command', function () {
    $this->artisan(WorldInstallCommand::class, ['--worldables' => true])
        ->assertExitCode(0);
});

it('runs migrations when installing', function () {
    // Verify migrations are run by checking worldables table is created
    $this->artisan(WorldInstallCommand::class, ['--worldables' => true])
        ->assertExitCode(0);

    expect(Schema::hasTable('worldables'))->toBeTrue();
});
