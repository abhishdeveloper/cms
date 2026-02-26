<?php

// Load Composer Autoloader if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/models/',
        __DIR__ . '/controllers/',
        __DIR__ . '/services/',
        __DIR__ . '/config/',
        __DIR__ . '/middleware/',
        __DIR__ . '/api/v1/middleware/',
        __DIR__ . '/api/v1/controllers/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
