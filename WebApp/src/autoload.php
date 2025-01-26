<?php

spl_autoload_register(function ($class) {
    // Replace namespace separator with directory separator
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    // Base directory for the namespace
    $baseDir = __DIR__ . '/';

    // Full path to the class file
    $file = $baseDir . $classPath . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
