<?php

namespace CleaniqueCoders\Traitify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CleaniqueCoders\Traitify\Traitify
 */
class Traitify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\Traitify\Traitify::class;
    }
}
