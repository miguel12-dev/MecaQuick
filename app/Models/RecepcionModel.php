<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Recepciones orden de trabajo (v1.0). Vinculada a inspección.
 * Datos adicionales del formulario se guardan en nota_cliente como JSON.
 */
final class RecepcionModel extends BaseModel
{
    private const TABLE = 'recepciones_orden_trabajo';

    public function obtenerPorCitaId(int $citaId): ?array
    {
        $row = $this->fetchOne(
            'SELECT rot.* FROM ' . self::TABLE . ' rot
             INNER JOIN inspecciones i ON i.id = rot.inspeccion_id
             WHERE i.cita_id = :cita_id LIMIT 1',
            [':cita_id' => $citaId]
        );
        if ($row === null) {
            return null;
        }
        return $this->normalizarParaVista($row);
    }

    public function obtenerPorInspeccionId(int $inspeccionId): ?array
    {
        $row = $this->fetchOne(
            'SELECT * FROM ' . self::TABLE . ' WHERE inspeccion_id = :id LIMIT 1',
            [':id' => $inspeccionId]
        );
        return $row !== null ? $this->normalizarParaVista($row) : null;
    }

    /**
     * @param array<string, mixed> $datos
     */
    public function guardarOActualizar(int $inspeccionId, array $datos, ?int $asesorServicioId = null): void
    {
        $existente = $this->obtenerPorInspeccionId($inspeccionId);

        $datosExtra = [
            'accesorios_internos' => $datos['accesorios_internos'] ?? [],
            'accesorios_externos' => $datos['accesorios_externos'] ?? [],
            'presupuesto_repuestos' => (float) ($datos['presupuesto_repuestos'] ?? 0),
            'presupuesto_mano_obra' => (float) ($datos['presupuesto_mano_obra'] ?? 0),
            'presupuesto_total' => (float) ($datos['presupuesto_total'] ?? 0),
            'metodo_pago' => $datos['metodo_pago'] ?? 'efectivo',
            'recibo_repuesto_cambiados' => (int) (($datos['recibo_repuesto_cambiados'] ?? 0) === 1),
            'defectos_carroceria' => $datos['defectos_carroceria'] ?? null,
            'inventariado_por' => $datos['inventariado_por'] ?? null,
            'inventariado_cc' => $datos['inventariado_cc'] ?? null,
            'firma_cliente' => $datos['firma_cliente'] ?? null,
            'firma_cliente_cc' => $datos['firma_cliente_cc'] ?? null,
            'autorizacion_adicional' => isset($datos['autorizacion_adicional']) ? (float) $datos['autorizacion_adicional'] : null,
            'vehiculo_conducido_por' => $datos['vehiculo_conducido_por'] ?? 'dueno',
            'fecha_servicio_anterior' => $datos['fecha_servicio_anterior'] ?? null,
            'or_numero' => $datos['or_numero'] ?? null,
            'tipo_servicio_anterior' => $datos['tipo_servicio_anterior'] ?? null,
            'km_servicio_anterior' => isset($datos['km_servicio_anterior']) ? (int) $datos['km_servicio_anterior'] : null,
        ];
        $notaCliente = json_encode($datosExtra, JSON_UNESCAPED_UNICODE);

        $km = $this->int($datos['kilometraje_recepcion'] ?? null);
        $observaciones = $this->str($datos['observaciones'] ?? null);
        if (!empty($datosExtra['defectos_carroceria'])) {
            $observaciones = trim(($observaciones ?? '') . "\nDefectos carrocería: " . $datosExtra['defectos_carroceria']);
        }

        $numeroOt = $existente['numero_ot'] ?? ('OT-' . date('Ymd') . '-' . str_pad((string) $inspeccionId, 4, '0', STR_PAD_LEFT));
        $fechaHoy = date('Y-m-d');
        $horaAhora = date('H:i');

        if ($existente === null) {
            $this->executeStatement(
                'INSERT INTO ' . self::TABLE . ' (
                    inspeccion_id, numero_ot, fecha_apertura, hora_apertura,
                    fecha_entrada, hora_entrada, kilometros_entrada,
                    observaciones_recepcion, nota_cliente, asesor_servicio_id
                ) VALUES (
                    :inspeccion_id, :numero_ot, :fecha_apertura, :hora_apertura,
                    :fecha_entrada, :hora_entrada, :kilometros_entrada,
                    :observaciones_recepcion, :nota_cliente, :asesor_servicio_id
                )',
                [
                    ':inspeccion_id' => $inspeccionId,
                    ':numero_ot' => $numeroOt,
                    ':fecha_apertura' => $fechaHoy,
                    ':hora_apertura' => $horaAhora,
                    ':fecha_entrada' => $fechaHoy,
                    ':hora_entrada' => $horaAhora,
                    ':kilometros_entrada' => $km,
                    ':observaciones_recepcion' => $observaciones,
                    ':nota_cliente' => $notaCliente,
                    ':asesor_servicio_id' => $asesorServicioId,
                ]
            );
            return;
        }

        $this->executeStatement(
            'UPDATE ' . self::TABLE . ' SET
                numero_ot = :numero_ot,
                fecha_entrada = :fecha_entrada,
                hora_entrada = :hora_entrada,
                kilometros_entrada = :kilometros_entrada,
                observaciones_recepcion = :observaciones_recepcion,
                nota_cliente = :nota_cliente,
                asesor_servicio_id = COALESCE(:asesor_servicio_id, asesor_servicio_id)
             WHERE inspeccion_id = :inspeccion_id',
            [
                ':inspeccion_id' => $inspeccionId,
                ':numero_ot' => $numeroOt,
                ':fecha_entrada' => $fechaHoy,
                ':hora_entrada' => $horaAhora,
                ':kilometros_entrada' => $km,
                ':observaciones_recepcion' => $observaciones,
                ':nota_cliente' => $notaCliente,
                ':asesor_servicio_id' => $asesorServicioId,
            ]
        );
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizarParaVista(array $row): array
    {
        $out = [
            'kilometraje_recepcion' => $row['kilometros_entrada'] ?? null,
            'observaciones' => $row['observaciones_recepcion'] ?? null,
        ];
        $nota = $row['nota_cliente'] ?? null;
        if (is_string($nota)) {
            $extra = json_decode($nota, true);
            if (is_array($extra)) {
                $out = array_merge($out, $extra);
            }
        }
        $out['accesorios_internos'] = $out['accesorios_internos'] ?? [];
        $out['accesorios_externos'] = $out['accesorios_externos'] ?? [];
        return $out;
    }

    private function str(?string $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        return trim($v);
    }

    private function int(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        $n = (int) $v;
        return $n >= 0 ? $n : null;
    }
}
