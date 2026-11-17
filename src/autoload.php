<?php
declare(strict_types=1);

/**
 * MARTELLO — Dahili PSR-4 Autoloader (ZeroDay)
 * Composer kurulu olmasa bile sınıfları doğrudan src/ ve tests/ altından yükler.
 */

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Martello\\Tests\\' => __DIR__ . '/../tests/',
        'Martello\\'       => __DIR__ . '/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
