<?php

namespace CleaniqueCoders\Traitify;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CleaniqueCoders\Traitify\Commands\TraitifyCommand;

class TraitifyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('traitify')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_traitify_table')
            ->hasCommand(TraitifyCommand::class);
    }
}
