<?php

declare(strict_types=1);

/**
 * MecaQuick - Único punto de entrada.
 * Todo el tráfico HTTP pasa por aquí vía .htaccess o como router de php -S.
 */

// Evitar acceso directo si se invoca fuera del servidor web
if (php_sapi_name() === 'cli') {
    exit('Este script solo puede ejecutarse desde el servidor web.');
}

// php -S: permitir que assets estáticos se sirvan directamente
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = __DIR__ . $uri;
if ($uri !== '/' && $uri !== '' && $uri !== '/index.php' && is_file($path)) {
    return false;
}

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/config/app.php';

$router = new \Core\Router();
$router->dispatch();
