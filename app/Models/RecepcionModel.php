<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Recepción del vehículo (vinculada a cita).
 */
final class RecepcionModel extends BaseModel
{
    public function obtenerPorCitaId(int $citaId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM recepcion WHERE cita_id = :id LIMIT 1',
            [':id' => $citaId]
        );
    }

    /**
     * @param array<string, mixed> $datos
     */
    public function guardarOActualizar(int $citaId, array $datos, ?int $inspeccionId = null): void
    {
        $existente = $this->obtenerPorCitaId($citaId);

        $accesoriosInt = isset($datos['accesorios_internos']) ? json_encode($datos['accesorios_internos']) : null;
        $accesoriosExt = isset($datos['accesorios_externos']) ? json_encode($datos['accesorios_externos']) : null;

        $params = [
            ':cita_id' => $citaId,
            ':inspeccion_id' => $inspeccionId,
            ':kilometraje_recepcion' => $this->int($datos['kilometraje_recepcion'] ?? null),
            ':fecha_servicio_anterior' => $this->date($datos['fecha_servicio_anterior'] ?? null),
            ':or_numero' => $this->str($datos['or_numero'] ?? null),
            ':tipo_servicio_anterior' => $this->str($datos['tipo_servicio_anterior'] ?? null),
            ':km_servicio_anterior' => $this->int($datos['km_servicio_anterior'] ?? null),
            ':vehiculo_conducido_por' => $this->str($datos['vehiculo_conducido_por'] ?? 'dueno'),
            ':presupuesto_repuestos' => (float) ($datos['presupuesto_repuestos'] ?? 0),
            ':presupuesto_mano_obra' => (float) ($datos['presupuesto_mano_obra'] ?? 0),
            ':presupuesto_total' => (float) ($datos['presupuesto_total'] ?? 0),
            ':metodo_pago' => $this->str($datos['metodo_pago'] ?? 'efectivo'),
            ':accesorios_internos' => $accesoriosInt,
            ':accesorios_externos' => $accesoriosExt,
            ':recibo_repuesto_cambiados' => (int) (($datos['recibo_repuesto_cambiados'] ?? 0) === 1 || ($datos['recibo_repuesto_cambiados'] ?? '') === 'si'),
            ':observaciones' => $this->str($datos['observaciones'] ?? null),
            ':defectos_carroceria' => $this->str($datos['defectos_carroceria'] ?? null),
            ':inventariado_por' => $this->str($datos['inventariado_por'] ?? null),
            ':inventariado_cc' => $this->str($datos['inventariado_cc'] ?? null),
            ':firma_cliente' => $this->str($datos['firma_cliente'] ?? null),
            ':firma_cliente_cc' => $this->str($datos['firma_cliente_cc'] ?? null),
            ':autorizacion_adicional' => $this->float($datos['autorizacion_adicional'] ?? null),
        ];

        if ($existente === null) {
            $this->executeStatement(
                'INSERT INTO recepcion (
                    cita_id, inspeccion_id, kilometraje_recepcion,
                    fecha_servicio_anterior, or_numero, tipo_servicio_anterior, km_servicio_anterior,
                    vehiculo_conducido_por, presupuesto_repuestos, presupuesto_mano_obra, presupuesto_total,
                    metodo_pago, accesorios_internos, accesorios_externos, recibo_repuesto_cambiados,
                    observaciones, defectos_carroceria, inventariado_por, inventariado_cc,
                    firma_cliente, firma_cliente_cc, autorizacion_adicional
                ) VALUES (
                    :cita_id, :inspeccion_id, :kilometraje_recepcion,
                    :fecha_servicio_anterior, :or_numero, :tipo_servicio_anterior, :km_servicio_anterior,
                    :vehiculo_conducido_por, :presupuesto_repuestos, :presupuesto_mano_obra, :presupuesto_total,
                    :metodo_pago, :accesorios_internos, :accesorios_externos, :recibo_repuesto_cambiados,
                    :observaciones, :defectos_carroceria, :inventariado_por, :inventariado_cc,
                    :firma_cliente, :firma_cliente_cc, :autorizacion_adicional
                )',
                $params
            );
            return;
        }

        $this->executeStatement(
            'UPDATE recepcion SET
                inspeccion_id = :inspeccion_id,
                kilometraje_recepcion = :kilometraje_recepcion,
                fecha_servicio_anterior = :fecha_servicio_anterior,
                or_numero = :or_numero,
                tipo_servicio_anterior = :tipo_servicio_anterior,
                km_servicio_anterior = :km_servicio_anterior,
                vehiculo_conducido_por = :vehiculo_conducido_por,
                presupuesto_repuestos = :presupuesto_repuestos,
                presupuesto_mano_obra = :presupuesto_mano_obra,
                presupuesto_total = :presupuesto_total,
                metodo_pago = :metodo_pago,
                accesorios_internos = :accesorios_internos,
                accesorios_externos = :accesorios_externos,
                recibo_repuesto_cambiados = :recibo_repuesto_cambiados,
                observaciones = :observaciones,
                defectos_carroceria = :defectos_carroceria,
                inventariado_por = :inventariado_por,
                inventariado_cc = :inventariado_cc,
                firma_cliente = :firma_cliente,
                firma_cliente_cc = :firma_cliente_cc,
                autorizacion_adicional = :autorizacion_adicional,
                updated_at = CURRENT_TIMESTAMP
             WHERE cita_id = :cita_id',
            $params
        );
    }

    public function actualizarInspeccionId(int $citaId, int $inspeccionId): void
    {
        $this->executeStatement(
            'UPDATE recepcion SET inspeccion_id = :inspeccion_id WHERE cita_id = :cita_id',
            [':inspeccion_id' => $inspeccionId, ':cita_id' => $citaId]
        );
    }

    private function str(?string $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        return trim($v);
    }

    private function date(?string $v): ?string
    {
        if ($v === null || $v === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string) $v))) {
            return null;
        }
        return trim((string) $v);
    }

    private function int(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        $n = (int) $v;
        return $n >= 0 ? $n : null;
    }

    private function float(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        return (float) $v;
    }
}
