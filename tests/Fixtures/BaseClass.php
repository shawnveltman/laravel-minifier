<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

class BaseClass
{
    public function parentMethod(): string
    {
        return 'Parent Method';
    }

    protected function parentProtectedMethod()
    {
        // Protected Method content
    }
}
