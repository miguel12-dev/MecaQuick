<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Resultados por punto de inspección (resultados_puntos).
 * Mapeo: OK/realizado=bueno, No OK=malo, Subsanada=regular.
 */
final class ResultadosPuntosModel extends BaseModel
{
    private const MAPEO_ESTADO = [
        'bueno' => 'bueno',
        'regular' => 'regular',
        'malo' => 'malo',
        'no_aplica' => 'no_aplica',
        'si' => 'bueno',
        'ok' => 'bueno',
        'no' => 'malo',
        'subsanado' => 'regular',
    ];

    /**
     * Guarda o actualiza el resultado de un punto.
     */
    public function guardarPunto(
        int $inspeccionId,
        int $puntoId,
        string $estado,
        ?string $valorMedido = null,
        ?string $observacion = null
    ): void {
        $estadoNormalizado = self::MAPEO_ESTADO[strtolower($estado)] ?? 'bueno';

        $this->executeStatement(
            'INSERT INTO resultados_puntos (inspeccion_id, punto_id, valor_medido, estado, observacion)
             VALUES (:inspeccion_id, :punto_id, :valor_medido, :estado, :observacion)
             ON DUPLICATE KEY UPDATE
                valor_medido = VALUES(valor_medido),
                estado = VALUES(estado),
                observacion = VALUES(observacion),
                registrado_at = CURRENT_TIMESTAMP',
            [
                ':inspeccion_id' => $inspeccionId,
                ':punto_id' => $puntoId,
                ':valor_medido' => $valorMedido,
                ':estado' => $estadoNormalizado,
                ':observacion' => $observacion,
            ]
        );
    }

    /**
     * Guarda múltiples resultados en batch.
     *
     * @param array<int, array{estado: string, valor_medido?: string, observacion?: string}> $resultados
     *   clave = punto_id
     */
    public function guardarBatch(int $inspeccionId, array $resultados): void
    {
        foreach ($resultados as $puntoId => $datos) {
            $this->guardarPunto(
                $inspeccionId,
                (int) $puntoId,
                $datos['estado'] ?? 'bueno',
                $datos['valor_medido'] ?? null,
                $datos['observacion'] ?? null
            );
        }
    }

    /**
     * Obtiene los resultados guardados por inspección, indexados por punto_id.
     *
     * @return array<int, array{estado: string, valor_medido: string|null, observacion: string|null}>
     */
    public function obtenerPorInspeccion(int $inspeccionId): array
    {
        $rows = $this->fetchAll(
            'SELECT punto_id, estado, valor_medido, observacion
             FROM resultados_puntos
             WHERE inspeccion_id = :id',
            [':id' => $inspeccionId]
        );
        $map = [];
        foreach ($rows as $r) {
            $puntoId = (int) ($r['punto_id'] ?? 0);
            if ($puntoId < 1) {
                continue;
            }
            $map[$puntoId] = [
                'estado' => (string) ($r['estado'] ?? ''),
                'valor_medido' => isset($r['valor_medido']) ? (string) $r['valor_medido'] : null,
                'observacion' => isset($r['observacion']) ? (string) $r['observacion'] : null,
            ];
        }
        return $map;
    }

    public function contarPorInspeccion(int $inspeccionId): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total FROM resultados_puntos WHERE inspeccion_id = :id',
            [':id' => $inspeccionId]
        );
        return (int) ($row['total'] ?? 0);
    }
}
