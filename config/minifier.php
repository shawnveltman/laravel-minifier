<?php

// config for Shawnveltman/LaravelMinifier
return [
    'path' => env('MINIFIER_PATH', 'ai_rag/classes.txt'),
    'disk' => env('MINIFIER_DISK', 'local'),
    'namespaces' => [
        'App',
        'Shawnveltman\LaravelMinifier\Tests\Fixtures',
        // Add additional namespaces as needed...
    ],
];
