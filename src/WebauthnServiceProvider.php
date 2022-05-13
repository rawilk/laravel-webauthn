<?php

namespace Rawilk\Webauthn;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Rawilk\Webauthn\Commands\WebauthnCommand;

class WebauthnServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-webauthn')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-webauthn_table')
            ->hasCommand(WebauthnCommand::class);
    }
}
