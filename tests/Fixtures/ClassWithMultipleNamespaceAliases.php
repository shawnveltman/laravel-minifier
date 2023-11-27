<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

use Some\Namespace\ClassA as First;
use Another\Namespace\ClassB as Second;

class ClassWithMultipleNamespaceAliases {
    public function methodWithMultipleAliases(First $first, Second $second) {
        // Implementation...
    }
}
