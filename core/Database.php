<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

/**
 * Conexión PDO a MySQL. Singleton para una única instancia por request.
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = require ROOT_PATH . '/config/database.php';
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        try {
            self::$instance = new PDO($dsn, $config['user'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Error de conexión a la base de datos: ' . $e->getMessage(), 0, $e);
        }

        return self::$instance;
    }

    public static function resetConnection(): void
    {
        self::$instance = null;
    }
}
