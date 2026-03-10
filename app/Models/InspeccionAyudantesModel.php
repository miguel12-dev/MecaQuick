<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Ayudantes o integrantes del grupo de revisión por inspección.
 */
final class InspeccionAyudantesModel extends BaseModel
{
    /**
     * Añade un aprendiz como ayudante. No duplica si ya existe.
     */
    public function agregar(int $inspeccionId, int $aprendizId): void
    {
        $this->executeStatement(
            'INSERT IGNORE INTO inspeccion_ayudantes (inspeccion_id, aprendiz_id) VALUES (:inspeccion_id, :aprendiz_id)',
            [':inspeccion_id' => $inspeccionId, ':aprendiz_id' => $aprendizId]
        );
    }

    /**
     * Añade varios aprendices como ayudantes (omite duplicados y al responsable).
     *
     * @param int[] $aprendizIds
     */
    public function agregarVarios(int $inspeccionId, int $responsableAprendizId, array $aprendizIds): void
    {
        foreach ($aprendizIds as $aid) {
            $aid = (int) $aid;
            if ($aid > 0 && $aid !== $responsableAprendizId) {
                $this->agregar($inspeccionId, $aid);
            }
        }
    }

    /**
     * Lista ayudantes de una inspección (id, nombre).
     *
     * @return array<int, array{id: int, nombre: string}>
     */
    public function listarPorInspeccion(int $inspeccionId): array
    {
        $rows = $this->fetchAll(
            'SELECT u.id, u.nombre FROM inspeccion_ayudantes ia
             INNER JOIN usuarios_sistema u ON u.id = ia.aprendiz_id
             WHERE ia.inspeccion_id = :id ORDER BY u.nombre ASC',
            [':id' => $inspeccionId]
        );
        $out = [];
        foreach ($rows as $r) {
            $out[] = ['id' => (int) $r['id'], 'nombre' => $r['nombre'] ?? ''];
        }
        return $out;
    }
}
