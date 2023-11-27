<?php

use Illuminate\Support\Facades\Config;
use Shawnveltman\LaravelMinifier\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

uses()->beforeEach(function () {
    // Mock configuration used by the Package
    Config::set('minifier.disk', 'local');
    Config::set('minifier.path', 'output.php');
});
