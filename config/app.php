<?php

declare(strict_types=1);

/**
 * Bootstrap de la aplicación.
 * Carga configuración, autoload y prepara el entorno.
 */

// Cargar variables de entorno desde .env (sin dependencias externas)
$envFile = ROOT_PATH . '/.env';
if (is_file($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        [$name, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B'\"");
        if ($name === '') {
            continue;
        }
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
    }
}

error_reporting(E_ALL);
ini_set('display_errors', getenv('APP_DEBUG') === '1' ? '1' : '0');

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Core\\' => ROOT_PATH . '/core/',
        'App\\'  => ROOT_PATH . '/app/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relative = substr($class, $len);
        $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

