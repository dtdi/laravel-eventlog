<?php

namespace Dtdi\Eventlog;

use Carbon\Carbon;

class EventAttributeBag
{
    public function __construct(
        public string|int $id,
        public string $eventName,
        public Carbon $timestamp,
        public array $attributes
    ) {
    }
}
