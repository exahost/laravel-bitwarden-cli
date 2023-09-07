<?php

namespace Aleex1848\LaravelBitwardenCli;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Aleex1848\LaravelBitwardenCli\Commands\LaravelBitwardenCliCommand;

class LaravelBitwardenCliServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-bitwarden-cli')
            ->hasConfigFile();
            //->hasViews()
            //->hasMigration('create_laravel-bitwarden-cli_table')
            //->hasCommand(LaravelBitwardenCliCommand::class);
    }    
}
