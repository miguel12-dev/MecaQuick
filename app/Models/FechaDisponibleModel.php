<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

final class FechaDisponibleModel extends BaseModel
{
    /**
     * Lista fechas activas desde hoy con número de citas asociadas.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarConOcupacion(): array
    {
        $sql = <<<'SQL'
            SELECT
                f.id,
                f.fecha,
                f.max_cupos,
                f.activa,
                COALESCE(COUNT(c.id), 0) AS citas_usadas
            FROM fechas_disponibles f
            LEFT JOIN citas c
                ON c.fecha_id = f.id
               AND c.estado IN ('pendiente','confirmada','reagendada','completada')
            WHERE f.fecha >= CURDATE()
            GROUP BY f.id, f.fecha, f.max_cupos, f.activa
            ORDER BY f.fecha ASC
        SQL;

        return $this->fetchAll($sql);
    }

    public function tieneCupo(int $fechaId, int $maxCuposDia): bool
    {
        $sql = <<<'SQL'
            SELECT
                f.max_cupos,
                f.activa,
                COALESCE(COUNT(c.id), 0) AS citas_usadas
            FROM fechas_disponibles f
            LEFT JOIN citas c
                ON c.fecha_id = f.id
               AND c.estado IN ('pendiente','confirmada','reagendada','completada')
            WHERE f.id = :id
            GROUP BY f.id, f.max_cupos, f.activa
        SQL;

        $fila = $this->fetchOne($sql, [':id' => $fechaId]);

        if ($fila === null || (int) $fila['activa'] !== 1) {
            return false;
        }

        $limite = min((int) $fila['max_cupos'], $maxCuposDia);

        return (int) $fila['citas_usadas'] < $limite;
    }
}

