<?php

namespace JeffersonGoncalves\QuebecVendors\Tests;

use JeffersonGoncalves\QuebecVendors\QuebecVendorsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            QuebecVendorsServiceProvider::class,
        ];
    }
}
