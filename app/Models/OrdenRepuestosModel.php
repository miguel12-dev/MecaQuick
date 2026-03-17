<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Órdenes de repuestos vinculadas a inspecciones.
 */
final class OrdenRepuestosModel extends BaseModel
{
    /**
     * Lista órdenes pendientes del aprendiz (inspecciones donde es responsable o ayudante).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPendientesPorAprendiz(int $aprendizId): array
    {
        return $this->fetchAll(
            'SELECT orp.id, orp.inspeccion_id, orp.placa, orp.modelo, orp.cliente_nombre,
                    orp.fecha_entrada, orp.estado, orp.created_at,
                    i.aprendiz_id, i.porcentaje_avance
             FROM ordenes_repuestos orp
             INNER JOIN inspecciones i ON i.id = orp.inspeccion_id
             LEFT JOIN inspeccion_ayudantes ia ON ia.inspeccion_id = i.id AND ia.aprendiz_id = :aid1
             WHERE (i.aprendiz_id = :aid2 OR ia.inspeccion_id IS NOT NULL)
             ORDER BY orp.created_at DESC',
            [':aid1' => $aprendizId, ':aid2' => $aprendizId]
        );
    }

    public function obtenerPorInspeccionId(int $inspeccionId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM ordenes_repuestos WHERE inspeccion_id = :id LIMIT 1',
            [':id' => $inspeccionId]
        );
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM ordenes_repuestos WHERE id = :id LIMIT 1', [':id' => $id]);
    }

    /**
     * Crea orden desde datos de checklist. Retorna id de la orden creada.
     */
    public function crearDesdeInspeccion(int $inspeccionId, array $datos): int
    {
        $this->executeStatement(
            'INSERT INTO ordenes_repuestos (
                inspeccion_id, cliente_nombre, cliente_documento, cliente_direccion, cliente_ciudad,
                cliente_telefono, cliente_celular, cliente_email, vin, numero_motor, placa, modelo,
                color, ano, fecha_entrada, hora_entrada, fecha_prometida, hora_prometida,
                mto_km, rep_gral, total, firma_recepcionista, firma_cliente, cc_recepcionista, cc_cliente
            ) VALUES (
                :inspeccion_id, :cliente_nombre, :cliente_documento, :cliente_direccion, :cliente_ciudad,
                :cliente_telefono, :cliente_celular, :cliente_email, :vin, :numero_motor, :placa, :modelo,
                :color, :ano, :fecha_entrada, :hora_entrada, :fecha_prometida, :hora_prometida,
                :mto_km, :rep_gral, 0, :firma_recepcionista, :firma_cliente, :cc_recepcionista, :cc_cliente
            )',
            [
                ':inspeccion_id' => $inspeccionId,
                ':cliente_nombre' => $datos['cliente_nombre'] ?? null,
                ':cliente_documento' => $datos['cliente_documento'] ?? null,
                ':cliente_direccion' => $datos['cliente_direccion'] ?? null,
                ':cliente_ciudad' => $datos['cliente_ciudad'] ?? null,
                ':cliente_telefono' => $datos['cliente_telefono'] ?? null,
                ':cliente_celular' => $datos['cliente_celular'] ?? null,
                ':cliente_email' => $datos['cliente_email'] ?? null,
                ':vin' => $datos['vin'] ?? null,
                ':numero_motor' => $datos['numero_motor'] ?? null,
                ':placa' => $datos['placa'] ?? null,
                ':modelo' => $datos['modelo'] ?? null,
                ':color' => $datos['color'] ?? null,
                ':ano' => $datos['ano'] ?? null,
                ':fecha_entrada' => $datos['fecha_entrada'] ?? null,
                ':hora_entrada' => $datos['hora_entrada'] ?? null,
                ':fecha_prometida' => $datos['fecha_prometida'] ?? null,
                ':hora_prometida' => $datos['hora_prometida'] ?? null,
                ':mto_km' => $datos['mto_km'] ?? null,
                ':rep_gral' => $datos['rep_gral'] ?? null,
                ':firma_recepcionista' => $datos['firma_recepcionista'] ?? null,
                ':firma_cliente' => $datos['firma_cliente'] ?? null,
                ':cc_recepcionista' => $datos['cc_recepcionista'] ?? null,
                ':cc_cliente' => $datos['cc_cliente'] ?? null,
            ]
        );
        return (int) $this->lastInsertId();
    }

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $id, array $datos): void
    {
        $this->executeStatement(
            'UPDATE ordenes_repuestos SET
                cliente_nombre = :cliente_nombre, cliente_documento = :cliente_documento,
                cliente_direccion = :cliente_direccion, cliente_ciudad = :cliente_ciudad,
                cliente_telefono = :cliente_telefono, cliente_celular = :cliente_celular,
                cliente_email = :cliente_email, vin = :vin, numero_motor = :numero_motor,
                placa = :placa, modelo = :modelo, color = :color, ano = :ano,
                fecha_entrada = :fecha_entrada, hora_entrada = :hora_entrada,
                fecha_prometida = :fecha_prometida, hora_prometida = :hora_prometida,
                mto_km = :mto_km, rep_gral = :rep_gral, total = :total,
                firma_recepcionista = :firma_recepcionista, firma_cliente = :firma_cliente,
                cc_recepcionista = :cc_recepcionista, cc_cliente = :cc_cliente,
                estado = :estado, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id',
            [
                ':id' => $id,
                ':cliente_nombre' => $datos['cliente_nombre'] ?? null,
                ':cliente_documento' => $datos['cliente_documento'] ?? null,
                ':cliente_direccion' => $datos['cliente_direccion'] ?? null,
                ':cliente_ciudad' => $datos['cliente_ciudad'] ?? null,
                ':cliente_telefono' => $datos['cliente_telefono'] ?? null,
                ':cliente_celular' => $datos['cliente_celular'] ?? null,
                ':cliente_email' => $datos['cliente_email'] ?? null,
                ':vin' => $datos['vin'] ?? null,
                ':numero_motor' => $datos['numero_motor'] ?? null,
                ':placa' => $datos['placa'] ?? null,
                ':modelo' => $datos['modelo'] ?? null,
                ':color' => $datos['color'] ?? null,
                ':ano' => $datos['ano'] ?? null,
                ':fecha_entrada' => $datos['fecha_entrada'] ?? null,
                ':hora_entrada' => $datos['hora_entrada'] ?? null,
                ':fecha_prometida' => $datos['fecha_prometida'] ?? null,
                ':hora_prometida' => $datos['hora_prometida'] ?? null,
                ':mto_km' => $datos['mto_km'] ?? null,
                ':rep_gral' => $datos['rep_gral'] ?? null,
                ':total' => (float) ($datos['total'] ?? 0),
                ':firma_recepcionista' => $datos['firma_recepcionista'] ?? null,
                ':firma_cliente' => $datos['firma_cliente'] ?? null,
                ':cc_recepcionista' => $datos['cc_recepcionista'] ?? null,
                ':cc_cliente' => $datos['cc_cliente'] ?? null,
                ':estado' => $datos['estado'] ?? 'pendiente',
            ]
        );
    }

    public function puedeVerAprendiz(int $ordenId, int $aprendizId): bool
    {
        $row = $this->fetchOne(
            'SELECT 1 FROM ordenes_repuestos orp
             INNER JOIN inspecciones i ON i.id = orp.inspeccion_id
             LEFT JOIN inspeccion_ayudantes ia ON ia.inspeccion_id = i.id AND ia.aprendiz_id = :aid1
             WHERE orp.id = :oid AND (i.aprendiz_id = :aid2 OR ia.aprendiz_id = :aid3)',
            [':oid' => $ordenId, ':aid1' => $aprendizId, ':aid2' => $aprendizId, ':aid3' => $aprendizId]
        );
        return $row !== null;
    }
}
