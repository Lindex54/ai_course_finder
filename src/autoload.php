<?php

declare(strict_types=1);

// Loads classes in the App\ namespace from src/, without Composer.
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $file = __DIR__ . DIRECTORY_SEPARATOR . $relative . '.php';

    if (is_file($file)) {
        require $file;
    }
});
