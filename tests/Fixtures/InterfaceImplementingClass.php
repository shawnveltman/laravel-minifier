<?php

namespace Shawnveltman\LaravelMinifier\Tests\Fixtures;

class InterfaceImplementingClass implements InterfaceToImplement
{
    public function interfaceMethod()
    {
        return 'implementation of interface method';
    }
}
