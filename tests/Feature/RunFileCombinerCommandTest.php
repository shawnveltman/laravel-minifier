<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

test('hello', function () {
    $directories = ['config','src','tests'];
    $blobPath = 'all_files.txt';

    foreach ($directories as $directory) {
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            File::append($blobPath, File::get($file));
        }
    }

    expect(true)->toBeTrue();
});
