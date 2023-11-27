<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Config;

class AliasesUseStatementClass
{
    public function methodWithAliasUse()
    {
        BaseCollection::times(3);
        Config::get('app.timezone');
    }
}
