<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

class DirectInstantiationClass
{
    public function methodWithInstantiation()
    {
        $instance = new OtherClass();
        $instance->doSomething();
    }
}
