<?php

namespace JeffersonGoncalves\QuebecVendors\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\QuebecVendors\QuebecVendors
 */
class QuebecVendors extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-quebec-vendors';
    }
}
