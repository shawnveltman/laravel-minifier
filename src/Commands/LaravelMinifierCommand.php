<?php

namespace Shawnveltman\LaravelMinifier\Commands;

use Illuminate\Console\Command;

class LaravelMinifierCommand extends Command
{
    public $signature = 'laravel-minifier';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
