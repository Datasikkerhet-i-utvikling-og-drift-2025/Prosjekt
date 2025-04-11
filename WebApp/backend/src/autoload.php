<?php

spl_autoload_register(function ($class) {
    // Konverter namespaces til path
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    // Base-dir for src-mappa i backend
    $baseDir = __DIR__ . '/';

    // Full path til klassen
    $file = $baseDir . $classPath . '.php';

    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("Autoloader: Fant ikke fil for klasse: $class (søkte etter $file)");
    }
});
