<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

class ChildClass extends BaseClass {
    public function childMethod() {
        $hello = $this->parentMethod();
        $this->parentProtectedMethod();
    }
}
