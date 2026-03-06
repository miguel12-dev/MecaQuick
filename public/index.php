<?php

declare(strict_types=1);

/**
 * MecaQuick - Único punto de entrada.
 * Todo el tráfico HTTP pasa por aquí vía .htaccess.
 */

// Evitar acceso directo si se invoca fuera del servidor web
if (php_sapi_name() === 'cli') {
    exit('Este script solo puede ejecutarse desde el servidor web.');
}

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/config/app.php';

$router = new \Core\Router();
$router->dispatch();
