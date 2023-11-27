<?php

namespace Shawnveltman\LaravelMinifier\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Shawnveltman\LaravelMinifier\LaravelMinifier
 */
class LaravelMinifier extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Shawnveltman\LaravelMinifier\LaravelMinifier::class;
    }
}
