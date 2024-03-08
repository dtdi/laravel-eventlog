<?php

namespace Dtdi\Eventlog;

class ObjectAttributeBag
{
    public function __construct(
        public string $objectId,
        public string $objectRelationType,
        public array $attributes
    ) {
    }
}
