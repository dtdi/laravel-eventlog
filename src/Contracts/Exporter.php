<?php

namespace Dtdi\Eventlog\Contracts;

use Dtdi\Eventlog\EventAttributeBag;

interface Exporter
{
    public function writeToFile(string $path): void;

    public function addEvent(EventAttributeBag $bag, ?array $objects);
}
