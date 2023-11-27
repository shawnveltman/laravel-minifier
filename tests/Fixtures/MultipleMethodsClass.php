<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

class MultipleMethodsClass
{
    public function firstMethod()
    {
        $this->secondMethod();
    }

    public function secondMethod()
    {
        // Second Method content
    }
}
