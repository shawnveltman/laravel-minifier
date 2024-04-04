<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

class TestClassWithTraitAliases
{
    use TestTrait {
        traitMethod as aliasedTraitMethod;
    }

    public function anotherMethod()
    {
        // ...
    }
}
