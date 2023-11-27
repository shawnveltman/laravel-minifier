<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

use Another\Namespace\ClassB as Second;
use Some\Namespace\ClassA as First;

class ClassWithMultipleNamespaceAliases
{
    public function methodWithMultipleAliases(First $first, Second $second)
    {
        // Implementation...
    }
}
