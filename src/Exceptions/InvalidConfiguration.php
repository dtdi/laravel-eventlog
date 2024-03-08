<?php

namespace Dtdi\Eventlog\Exceptions;

use Dtdi\Eventlog\Contracts\Event;
use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidConfiguration extends Exception
{
    public static function modelIsNotValid(string $className): self
    {
        return new static("The given model class `{$className}` does not implement `".Event::class.'` or it does not extend `'.Model::class.'`');
    }
}
