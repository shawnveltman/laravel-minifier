<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

use Illuminate\Support\Collection;

class UseStatementClass
{
    public function methodWithUse()
    {
        return Collection::make();
    }
}
