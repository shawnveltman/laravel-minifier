<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

class ChildClass extends BaseClass implements SomeInterface
{
    public function childMethod()
    {
        $hello = $this->parentMethod();
        $this->parentProtectedMethod();
    }

    public function interfaceMethod()
    {
        // TODO: Implement interfaceMethod() method.
    }
}
