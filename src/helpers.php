<?php

use Dtdi\Eventlog\EventLogger;

if (! function_exists('eventlog')) {
    function eventlog(): EventLogger
    {
        //$logStatus = app(ActivityLogStatus::class);

        return app(EventLogger::class);
    }
}
