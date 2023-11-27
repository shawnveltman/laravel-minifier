<?php

namespace Shawnveltman\LaravelMinifier;

use Shawnveltman\LaravelMinifier\Commands\LaravelMinifierCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMinifierServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-minifier')
            ->hasConfigFile()
//            ->hasViews()
//            ->hasMigration('create_laravel-minifier_table')
            ->hasCommand(LaravelMinifierCommand::class);
    }
}
