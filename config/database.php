<?php

declare(strict_types=1);

/**
 * Configuración de conexión MySQL.
 * Credenciales y datos sensibles solo en este archivo / .env.
 */

return [
    'host'     => getenv('DB_HOST') ?: 'localhost',
    'port'     => (int) (getenv('DB_PORT') ?: 3306),
    'dbname'   => getenv('DB_NAME') ?: 'mecaquick',
    'charset'  => getenv('DB_CHARSET') ?: 'utf8mb4',
    'user'     => getenv('DB_USER') ?: '',
    'password' => getenv('DB_PASS') ?: '',
];

