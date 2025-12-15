<?php

declare(strict_types=1);

namespace Ritechoice23\Worldable;

use Illuminate\Database\Eloquent\Relations\Relation;
use Ritechoice23\Worldable\Commands\WorldHealthCommand;
use Ritechoice23\Worldable\Commands\WorldInstallCommand;
use Ritechoice23\Worldable\Commands\WorldLinkCommand;
use Ritechoice23\Worldable\Commands\WorldUninstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WorldableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-worldable')
            ->hasConfigFile('worldable')
            ->hasCommands([
                WorldInstallCommand::class,
                WorldUninstallCommand::class,
                WorldLinkCommand::class,
                WorldHealthCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        $this->registerMorphMap();
    }

    private function registerMorphMap(): void
    {
        Relation::morphMap([
            'continent' => \Ritechoice23\Worldable\Models\Continent::class,
            'subregion' => \Ritechoice23\Worldable\Models\Subregion::class,
            'country' => \Ritechoice23\Worldable\Models\Country::class,
            'state' => \Ritechoice23\Worldable\Models\State::class,
            'city' => \Ritechoice23\Worldable\Models\City::class,
            'currency' => \Ritechoice23\Worldable\Models\Currency::class,
            'language' => \Ritechoice23\Worldable\Models\Language::class,
            'timezone' => \Ritechoice23\Worldable\Models\Timezone::class,
            'worldable' => \Ritechoice23\Worldable\Models\Worldable::class,
        ]);
    }
}
