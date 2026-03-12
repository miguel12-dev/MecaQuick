<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Resultados por punto de inspección (resultados_puntos).
 * v1.0: estado_id FK a estados_punto. Mapeo: OK/realizado=bueno, No OK=malo, Subsanada=regular.
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

    /** @var array<string, int>|null */
    private static ?array $estadoIds = null;

    private function obtenerEstadoId(string $nombre): int
    {
        if (self::$estadoIds === null) {
            $rows = $this->fetchAll('SELECT id, nombre FROM estados_punto');
            self::$estadoIds = [];
            foreach ($rows as $r) {
                self::$estadoIds[(string) $r['nombre']] = (int) $r['id'];
            }
        }
        return self::$estadoIds[$nombre] ?? self::$estadoIds['bueno'] ?? 1;
    }

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
        $estadoId = $this->obtenerEstadoId($estadoNormalizado);

        $this->executeStatement(
            'INSERT INTO resultados_puntos (inspeccion_id, punto_id, valor_medido, estado_id, observacion)
             VALUES (:inspeccion_id, :punto_id, :valor_medido, :estado_id, :observacion)
             ON DUPLICATE KEY UPDATE
                valor_medido = VALUES(valor_medido),
                estado_id = VALUES(estado_id),
                observacion = VALUES(observacion),
                registrado_at = CURRENT_TIMESTAMP',
            [
                ':inspeccion_id' => $inspeccionId,
                ':punto_id' => $puntoId,
                ':valor_medido' => $valorMedido,
                ':estado_id' => $estadoId,
                ':observacion' => $observacion,
            ]
        );
    }

    /**
     * Guarda múltiples resultados en batch.
     *
     * @param array<int, array{estado: string, valor_medido?: string, observacion?: string}> $resultados
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
}
