<?php

namespace Shawnveltman\LaravelMinifier\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LaravelMinifierCommand extends Command
{
    public $signature = 'laravel-minifier';

    public $description = 'My command';

    public function handle()
    {
        $directories = ['config', 'src', 'tests'];
        $blobPath = base_path('all_files.txt');

        foreach ($directories as $directory) {
            $files = File::allFiles($directory);

            foreach ($files as $file) {
                File::append($blobPath, File::get($file));
            }
        }

        $this->info('All files have been compiled into blob.txt.');
    }

    private function traverseDirectory($directory, $blobPath)
    {
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            File::append($blobPath, File::get($file));
        }
    }
}
