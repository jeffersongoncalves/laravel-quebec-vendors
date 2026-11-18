<?php

namespace JeffersonGoncalves\QuebecVendors;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QuebecVendorsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-quebec-vendors')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations();
    }
}
