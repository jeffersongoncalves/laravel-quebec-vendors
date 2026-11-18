<?php

namespace JeffersonGoncalves\QuebecVendors;

use JeffersonGoncalves\QuebecVendors\Commands\VendorsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QuebecVendorsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('quebec-vendors')
            ->hasConfigFile()
            ->hasMigrations(['create_quebec_vendors_table'])
            ->hasCommand(VendorsCommand::class);
    }
}
