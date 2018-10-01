<?php

namespace saberLiou\Whetstone\Facades;

use Illuminate\Support\Facades\Facade;

class Whetstone extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'whetstone';
    }
}
