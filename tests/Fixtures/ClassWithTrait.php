<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

use Shawnveltman\LaravelMinifier\Tests\Fixtures\ExampleTrait;

class ClassWithTrait
{
    use ExampleTrait;

    public function methodUsingTrait()
    {
        $this->traitMethod();
    }
}
