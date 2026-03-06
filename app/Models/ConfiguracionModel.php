<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Acceso a configuración del sistema (tabla configuracion) con caché estática.
 */
final class ConfiguracionModel extends BaseModel
{
    /**
     * @var array<string, mixed>
     */
    private static array $cache = [];

    /**
     * Obtiene el valor tipado de una clave de configuración.
     */
    public static function get(string $clave): mixed
    {
        if (array_key_exists($clave, self::$cache)) {
            return self::$cache[$clave];
        }

        $instance = new self();
        $fila = $instance->fetchOne(
            'SELECT valor, tipo FROM configuracion WHERE clave = :clave',
            [':clave' => $clave]
        );

        if ($fila === null) {
            self::$cache[$clave] = null;
            return null;
        }

        $valorBruto = (string) $fila['valor'];
        $tipo = (string) ($fila['tipo'] ?? 'texto');

        $valor = match ($tipo) {
            'numero' => is_numeric($valorBruto) ? (int) $valorBruto : 0,
            'booleano' => $valorBruto === '1' || strcasecmp($valorBruto, 'true') === 0,
            'json' => json_decode($valorBruto, true, 512, JSON_THROW_ON_ERROR),
            default => $valorBruto,
        };

        self::$cache[$clave] = $valor;

        return $valor;
    }

    /**
     * Establece un valor de configuración (no cambia el tipo).
     */
    public static function set(string $clave, string $valor): void
    {
        $instance = new self();
        $instance->executeStatement(
            'INSERT INTO configuracion (clave, valor) VALUES (:clave, :valor)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)',
            [
                ':clave' => $clave,
                ':valor' => $valor,
            ]
        );

        self::$cache[$clave] = $valor;
    }
}

