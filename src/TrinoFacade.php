<?php

namespace LaraserveTech;

use Illuminate\Support\Facades\Facade;

class TrinoFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'trino';
    }
}
