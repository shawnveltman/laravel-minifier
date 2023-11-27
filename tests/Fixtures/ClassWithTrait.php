<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

class ClassWithTrait
{
    use ExampleTrait;

    public function methodUsingTrait()
    {
        $this->traitMethod();
    }
}
