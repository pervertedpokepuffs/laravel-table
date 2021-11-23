<?php

namespace Sysniq\LaravelTable;

use Illuminate\Support\Facades\Facade;

class LaravelTableFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-table';
    }
}
