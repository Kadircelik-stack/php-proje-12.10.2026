<?php
declare(strict_types=1);

/**
 * MARTELLO — PSR-4 Uyumlu Bağımsız Autoloader (ZeroDay Ekibi)
 * Composer bağımlılığı olmadan yerel PHP ve sunucularda otomatik sınıf yüklemesi sağlar.
 */

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Martello\\Tests\\' => __DIR__ . '/../tests/',
        'Martello\\'       => __DIR__ . '/../src/',
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
