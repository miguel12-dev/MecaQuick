<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Inspecciones (con o sin cita). Checklist standalone usa token, cita_id y aprendiz_id NULL.
 */
final class InspeccionModel extends BaseModel
{
    public function obtenerPorToken(string $token): ?array
    {
        return $this->fetchOne(
            'SELECT id, token, estado, porcentaje_avance FROM inspecciones WHERE token = :token LIMIT 1',
            [':token' => $token]
        );
    }

    public function crearStandalone(string $token): int
    {
        $this->executeStatement(
            'INSERT INTO inspecciones (token, cita_id, aprendiz_id, estado, porcentaje_avance)
             VALUES (:token, NULL, NULL, :estado, 0)',
            [':token' => $token, ':estado' => 'en_proceso']
        );
        return (int) $this->lastInsertId();
    }

    public function actualizarAvance(int $inspeccionId, int $porcentajeAvance, bool $finalizada): void
    {
        $estado = $finalizada ? 'finalizada' : 'en_proceso';
        $this->executeStatement(
            'UPDATE inspecciones SET porcentaje_avance = :porcentaje, estado = :estado WHERE id = :id',
            [
                ':porcentaje' => $porcentajeAvance,
                ':estado' => $estado,
                ':id' => $inspeccionId,
            ]
        );
    }
}
