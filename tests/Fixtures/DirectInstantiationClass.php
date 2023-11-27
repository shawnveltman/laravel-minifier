<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

use Shawnveltman\LaravelMinifier\Tests\Fixtures\OtherClass;

class DirectInstantiationClass
{
    public function methodWithInstantiation()
    {
        $instance = new OtherClass();
        $instance->doSomething();
    }
}
