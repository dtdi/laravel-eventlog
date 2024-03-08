<?php

namespace Dtdi\Eventlog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dtdi\Eventlog\Eventlogger
 */
class Eventlog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Dtdi\Eventlog\EventLogger::class;
    }
}
