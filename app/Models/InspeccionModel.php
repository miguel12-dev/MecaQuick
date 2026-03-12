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
        $row = $this->fetchOne(
            'SELECT i.id, i.token, i.porcentaje_avance, e.nombre AS estado
             FROM inspecciones i
             INNER JOIN estados_inspeccion e ON e.id = i.estado_id
             WHERE i.token = :token LIMIT 1',
            [':token' => $token]
        );
        return $row;
    }

    public function crearStandalone(string $token, ?int $aprendizId = null): int
    {
        $estadoId = $this->obtenerEstadoInspeccionId('en_proceso');
        $this->executeStatement(
            'INSERT INTO inspecciones (token, cita_id, aprendiz_id, estado_id, porcentaje_avance, inicio_at)
             VALUES (:token, NULL, :aprendiz_id, :estado_id, 0, CURRENT_TIMESTAMP)',
            [
                ':token' => $token,
                ':aprendiz_id' => $aprendizId,
                ':estado_id' => $estadoId,
            ]
        );
        return (int) $this->lastInsertId();
    }

    /**
     * Crea una inspección desde el módulo de mantenimiento (aprendiz + tutor asignado).
     */
    public function crearDesdeMantenimiento(string $token, int $aprendizId, ?int $instructorId = null): int
    {
        $estadoId = $this->obtenerEstadoInspeccionId('en_proceso');
        $this->executeStatement(
            'INSERT INTO inspecciones (token, cita_id, aprendiz_id, instructor_id, estado_id, porcentaje_avance, inicio_at)
             VALUES (:token, NULL, :aprendiz_id, :instructor_id, :estado_id, 0, CURRENT_TIMESTAMP)',
            [
                ':token' => $token,
                ':aprendiz_id' => $aprendizId,
                ':instructor_id' => $instructorId,
                ':estado_id' => $estadoId,
            ]
        );
        return (int) $this->lastInsertId();
    }

    /**
     * Crear inspección desde cita (flujo recepción → checklist).
     * Si ya existe, devuelve la existente.
     */
    public function crearDesdeCita(int $citaId): array
    {
        $existente = $this->obtenerPorCitaId($citaId);
        if ($existente !== null) {
            return ['id' => (int) $existente['id'], 'token' => (string) $existente['token']];
        }
        $token = bin2hex(random_bytes(32));
        $estadoId = $this->obtenerEstadoInspeccionId('en_proceso');
        $this->executeStatement(
            'INSERT INTO inspecciones (token, cita_id, aprendiz_id, estado_id, porcentaje_avance)
             VALUES (:token, :cita_id, NULL, :estado_id, 0)',
            [':token' => $token, ':cita_id' => $citaId, ':estado_id' => $estadoId]
        );
        $id = (int) $this->lastInsertId();
        return ['id' => $id, 'token' => $token];
    }

    public function obtenerPorCitaId(int $citaId): ?array
    {
        $row = $this->fetchOne(
            'SELECT i.id, i.token, e.nombre AS estado
             FROM inspecciones i
             INNER JOIN estados_inspeccion e ON e.id = i.estado_id
             WHERE i.cita_id = :id LIMIT 1',
            [':id' => $citaId]
        );
        return $row;
    }

    public function actualizarAvance(int $inspeccionId, int $porcentajeAvance, bool $finalizada): void
    {
        $estadoNombre = $finalizada ? 'finalizada' : 'en_proceso';
        $estadoId = $this->obtenerEstadoInspeccionId($estadoNombre);
        $this->executeStatement(
            'UPDATE inspecciones SET porcentaje_avance = :porcentaje, estado_id = :estado_id WHERE id = :id',
            [
                ':porcentaje' => $porcentajeAvance,
                ':estado_id' => $estadoId,
                ':id' => $inspeccionId,
            ]
        );
    }

    private function obtenerEstadoInspeccionId(string $nombre): int
    {
        static $cache = null;
        if ($cache === null) {
            $rows = $this->fetchAll('SELECT id, nombre FROM estados_inspeccion');
            $cache = [];
            foreach ($rows as $r) {
                $cache[(string) $r['nombre']] = (int) $r['id'];
            }
        }
        return $cache[$nombre] ?? 1;
    }

    /**
     * Fechas (Y-m-d) con al menos una inspección (por inicio_at o checklist_datos.created_at).
     *
     * @return array<int, string>
     */
    public function fechasConRevisiones(): array
    {
        $rows = $this->fetchAll(
            'SELECT DISTINCT DATE(COALESCE(i.inicio_at, cd.created_at)) AS fecha
             FROM inspecciones i
             LEFT JOIN checklist_datos cd ON cd.inspeccion_id = i.id
             WHERE i.token IS NOT NULL
               AND (i.inicio_at IS NOT NULL OR cd.id IS NOT NULL)
             ORDER BY fecha DESC'
        );
        $fechas = [];
        foreach ($rows as $row) {
            $f = $row['fecha'] ?? null;
            if ($f !== null && $f !== '') {
                $fechas[] = $f;
            }
        }
        return $fechas;
    }

    /**
     * Lista revisiones (inspecciones) de un día para el panel del instructor.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarRevisionesPorFecha(string $fecha): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return [];
        }
        return $this->fetchAll(
            'SELECT i.id, i.token, i.porcentaje_avance, e.nombre AS estado,
                    COALESCE(i.inicio_at, cd.created_at) AS inicio_at,
                    cd.placa,
                    COALESCE(u.nombre, NULLIF(TRIM(cd.nombre_tecnico), \'\'), \'Sin asignar\') AS encargado
             FROM inspecciones i
             INNER JOIN estados_inspeccion e ON e.id = i.estado_id
             LEFT JOIN checklist_datos cd ON cd.inspeccion_id = i.id
             LEFT JOIN usuarios_sistema u ON u.id = i.aprendiz_id
             WHERE i.token IS NOT NULL
               AND (
                   (i.inicio_at IS NOT NULL AND DATE(i.inicio_at) = :fecha)
                   OR (i.inicio_at IS NULL AND cd.id IS NOT NULL AND DATE(cd.created_at) = :fecha2)
               )
             ORDER BY COALESCE(i.inicio_at, cd.created_at) ASC',
            [':fecha' => $fecha, ':fecha2' => $fecha]
        );
    }

    /**
     * Detalle de una inspección para la vista instructor: cabecera + resultados con evidencias.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerDetalleParaInstructor(int $id): ?array
    {
        $inspeccion = $this->fetchOne(
            'SELECT i.id, i.token, i.porcentaje_avance, e.nombre AS estado,
                    COALESCE(i.inicio_at, cd.created_at) AS inicio_at,
                    cd.placa,
                    COALESCE(u.nombre, NULLIF(TRIM(cd.nombre_tecnico), \'\'), \'Sin asignar\') AS encargado,
                    cd.placa AS numero_orden, cd.fecha_ingreso AS fecha_servicio
             FROM inspecciones i
             INNER JOIN estados_inspeccion e ON e.id = i.estado_id
             LEFT JOIN checklist_datos cd ON cd.inspeccion_id = i.id
             LEFT JOIN usuarios_sistema u ON u.id = i.aprendiz_id
             WHERE i.id = :id AND i.token IS NOT NULL',
            [':id' => $id]
        );
        if ($inspeccion === null) {
            return null;
        }

        $resultados = $this->fetchAll(
            'SELECT rp.id AS resultado_id, rp.punto_id, ep.nombre AS estado, rp.valor_medido, rp.observacion,
                    rp.tiene_evidencia, rp.registrado_at,
                    pc.numero_punto, pc.descripcion AS punto_descripcion
             FROM resultados_puntos rp
             INNER JOIN puntos_catalogo pc ON pc.id = rp.punto_id
             INNER JOIN estados_punto ep ON ep.id = rp.estado_id
             WHERE rp.inspeccion_id = :id
             ORDER BY pc.numero_punto ASC',
            [':id' => $id]
        );

        $resultadoIds = array_column($resultados, 'resultado_id');
        $evidenciasPorResultado = [];
        if ($resultadoIds !== []) {
            $placeholders = implode(',', array_fill(0, count($resultadoIds), '?'));
            $evidencias = $this->fetchAll(
                'SELECT resultado_punto_id, ruta_archivo FROM evidencias WHERE resultado_punto_id IN (' . $placeholders . ')',
                $resultadoIds
            );
            foreach ($evidencias as $ev) {
                $rid = (int) $ev['resultado_punto_id'];
                if (!isset($evidenciasPorResultado[$rid])) {
                    $evidenciasPorResultado[$rid] = [];
                }
                $evidenciasPorResultado[$rid][] = $ev['ruta_archivo'];
            }
        }

        foreach ($resultados as &$r) {
            $r['evidencias'] = $evidenciasPorResultado[(int) $r['resultado_id']] ?? [];
        }
        unset($r);

        $inspeccion['resultados'] = $resultados;
        return $inspeccion;
    }

    /**
     * Lista inspecciones donde el aprendiz es responsable o ayudante.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPorAprendiz(int $aprendizId): array
    {
        return $this->fetchAll(
            'SELECT i.id, i.token, i.porcentaje_avance, e.nombre AS estado,
                    COALESCE(i.inicio_at, cd.created_at) AS inicio_at,
                    cd.placa,
                    i.aprendiz_id = :aprendiz_id AS es_responsable
             FROM inspecciones i
             INNER JOIN estados_inspeccion e ON e.id = i.estado_id
             LEFT JOIN checklist_datos cd ON cd.inspeccion_id = i.id
             LEFT JOIN inspeccion_ayudantes ia ON ia.inspeccion_id = i.id AND ia.aprendiz_id = :aprendiz_id2
             WHERE i.token IS NOT NULL
               AND (i.aprendiz_id = :aprendiz_id3 OR ia.inspeccion_id IS NOT NULL)
             ORDER BY COALESCE(i.inicio_at, cd.created_at) DESC',
            [
                ':aprendiz_id' => $aprendizId,
                ':aprendiz_id2' => $aprendizId,
                ':aprendiz_id3' => $aprendizId,
            ]
        );
    }

    /**
     * Comprueba si el aprendiz puede ver la inspección (es responsable o ayudante).
     */
    public function puedeVerAprendiz(int $inspeccionId, int $aprendizId): bool
    {
        $row = $this->fetchOne(
            'SELECT 1 FROM inspecciones i
             LEFT JOIN inspeccion_ayudantes ia ON ia.inspeccion_id = i.id AND ia.aprendiz_id = :aprendiz_id
             WHERE i.id = :id AND (i.aprendiz_id = :aprendiz_id2 OR ia.inspeccion_id IS NOT NULL)
             LIMIT 1',
            [':id' => $inspeccionId, ':aprendiz_id' => $aprendizId, ':aprendiz_id2' => $aprendizId]
        );
        return $row !== null;
    }

    /**
     * Detalle de una inspección para vista solo lectura del aprendiz.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerDetalleParaAprendiz(int $inspeccionId, int $aprendizId): ?array
    {
        if (!$this->puedeVerAprendiz($inspeccionId, $aprendizId)) {
            return null;
        }
        return $this->obtenerDetalleParaInstructor($inspeccionId);
    }

    /**
     * Obtiene una inspección por ID (solo campos básicos) para validar propiedad y estado.
     *
     * @return array{id: int, aprendiz_id: int|null, estado: string}|null
     */
    public function obtenerBasica(int $id): ?array
    {
        $row = $this->fetchOne(
            'SELECT i.id, i.aprendiz_id, e.nombre AS estado
             FROM inspecciones i
             INNER JOIN estados_inspeccion e ON e.id = i.estado_id
             WHERE i.id = :id LIMIT 1',
            [':id' => $id]
        );
        if ($row === null) {
            return null;
        }
        $row['id'] = (int) $row['id'];
        $row['aprendiz_id'] = isset($row['aprendiz_id']) ? (int) $row['aprendiz_id'] : null;
        return $row;
    }
}
