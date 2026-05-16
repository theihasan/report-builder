<?php

$autoloaders = [
    __DIR__.'/../vendor/autoload.php',
    __DIR__.'/../../../vendor/autoload.php',
];

foreach ($autoloaders as $autoloader) {
    if (is_file($autoloader)) {
        require $autoloader;

        break;
    }
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'Ihasan\\ReportBuilder\\Tests\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relativePath = substr($class, strlen($prefix));
    $filePath = __DIR__.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $relativePath).'.php';

    if (is_file($filePath)) {
        require $filePath;
    }
});
