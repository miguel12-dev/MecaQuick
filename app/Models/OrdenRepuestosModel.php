<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Modelo para órdenes de repuestos.
 */
final class OrdenRepuestosModel extends BaseModel
{
    /**
     * Lista órdenes del aprendiz (por inspecciones donde participa).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPorAprendiz(int $aprendizId): array
    {
        $sql = 'SELECT orp.id, orp.placa, orp.cliente_nombre, orp.fecha_entrada, orp.total, orp.inspeccion_id
                FROM orden_repuestos orp
                LEFT JOIN inspecciones i ON i.id = orp.inspeccion_id
                LEFT JOIN inspeccion_ayudantes ia ON ia.inspeccion_id = orp.inspeccion_id AND ia.aprendiz_id = :aid2
                WHERE orp.aprendiz_id = :aid
                   OR i.aprendiz_id = :aid3
                   OR ia.aprendiz_id IS NOT NULL
                ORDER BY orp.created_at DESC';
        return $this->fetchAll($sql, [':aid' => $aprendizId, ':aid2' => $aprendizId, ':aid3' => $aprendizId]);
    }

    /**
     * Verifica si el aprendiz puede acceder a la orden.
     */
    public function puedeAccederAprendiz(int $ordenId, int $aprendizId): bool
    {
        $row = $this->fetchOne(
            'SELECT orp.id FROM orden_repuestos orp
             LEFT JOIN inspecciones i ON i.id = orp.inspeccion_id
             LEFT JOIN inspeccion_ayudantes ia ON ia.inspeccion_id = orp.inspeccion_id AND ia.aprendiz_id = :aid2
             WHERE orp.id = :id AND (orp.aprendiz_id = :aid OR i.aprendiz_id = :aid3 OR ia.aprendiz_id IS NOT NULL)',
            [':id' => $ordenId, ':aid' => $aprendizId, ':aid2' => $aprendizId, ':aid3' => $aprendizId]
        );
        return $row !== null;
    }

    /**
     * Obtiene una orden por ID con sus ítems.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerPorId(int $id): ?array
    {
        $row = $this->fetchOne(
            'SELECT * FROM orden_repuestos WHERE id = :id LIMIT 1',
            [':id' => $id]
        );
        if ($row === null) {
            return null;
        }
        $items = $this->fetchAll(
            'SELECT id, referencia, descripcion, cantidad_tiempo, precio_unitario
             FROM orden_repuestos_items WHERE orden_repuestos_id = :id ORDER BY id ASC',
            [':id' => $id]
        );
        $row['items'] = $items;
        return $row;
    }

    /**
     * Crea una orden y sus ítems.
     *
     * @param array<string, mixed> $datos
     * @param array<int, array{referencia?: string, descripcion: string, cantidad_tiempo: string, precio_unitario: float}> $items
     */
    public function crear(array $datos, array $items): int
    {
        $this->executeStatement(
            'INSERT INTO orden_repuestos (
                inspeccion_id, aprendiz_id, cliente_nombre, cliente_documento, cliente_direccion, cliente_ciudad,
                cliente_telefono, cliente_celular, cliente_email, vin, no_motor, placa, modelo, color, ano,
                fecha_entrada, hora_entrada, fecha_prometida, hora_prometida, km_mto, rep_gral,
                total, firma_recepcionista, firma_cliente, cc_recepcionista, cc_cliente
            ) VALUES (
                :inspeccion_id, :aprendiz_id, :cliente_nombre, :cliente_documento, :cliente_direccion, :cliente_ciudad,
                :cliente_telefono, :cliente_celular, :cliente_email, :vin, :no_motor, :placa, :modelo, :color, :ano,
                :fecha_entrada, :hora_entrada, :fecha_prometida, :hora_prometida, :km_mto, :rep_gral,
                :total, :firma_recepcionista, :firma_cliente, :cc_recepcionista, :cc_cliente
            )',
            [
                ':inspeccion_id' => $this->int($datos['inspeccion_id'] ?? null),
                ':aprendiz_id' => $this->int($datos['aprendiz_id'] ?? null),
                ':cliente_nombre' => (string) ($datos['cliente_nombre'] ?? ''),
                ':cliente_documento' => (string) ($datos['cliente_documento'] ?? ''),
                ':cliente_direccion' => $this->str($datos['cliente_direccion'] ?? null),
                ':cliente_ciudad' => $this->str($datos['cliente_ciudad'] ?? null),
                ':cliente_telefono' => $this->str($datos['cliente_telefono'] ?? null),
                ':cliente_celular' => $this->str($datos['cliente_celular'] ?? null),
                ':cliente_email' => $this->str($datos['cliente_email'] ?? null),
                ':vin' => $this->str($datos['vin'] ?? null),
                ':no_motor' => $this->str($datos['no_motor'] ?? null),
                ':placa' => (string) ($datos['placa'] ?? ''),
                ':modelo' => $this->str($datos['modelo'] ?? null),
                ':color' => $this->str($datos['color'] ?? null),
                ':ano' => $this->int($datos['ano'] ?? null),
                ':fecha_entrada' => (string) ($datos['fecha_entrada'] ?? date('Y-m-d')),
                ':hora_entrada' => $this->str($datos['hora_entrada'] ?? null),
                ':fecha_prometida' => $this->date($datos['fecha_prometida'] ?? null),
                ':hora_prometida' => $this->str($datos['hora_prometida'] ?? null),
                ':km_mto' => $this->int($datos['km_mto'] ?? null),
                ':rep_gral' => $this->str($datos['rep_gral'] ?? null),
                ':total' => (float) ($datos['total'] ?? 0),
                ':firma_recepcionista' => $this->str($datos['firma_recepcionista'] ?? null),
                ':firma_cliente' => $this->str($datos['firma_cliente'] ?? null),
                ':cc_recepcionista' => $this->str($datos['cc_recepcionista'] ?? null),
                ':cc_cliente' => $this->str($datos['cc_cliente'] ?? null),
            ]
        );
        $ordenId = (int) $this->lastInsertId();
        foreach ($items as $item) {
            $desc = trim((string) ($item['descripcion'] ?? ''));
            if ($desc === '') {
                continue;
            }
            $this->executeStatement(
                'INSERT INTO orden_repuestos_items (orden_repuestos_id, referencia, descripcion, cantidad_tiempo, precio_unitario)
                 VALUES (:oid, :ref, :desc, :cant, :precio)',
                [
                    ':oid' => $ordenId,
                    ':ref' => $this->str($item['referencia'] ?? null),
                    ':desc' => $desc,
                    ':cant' => (string) ($item['cantidad_tiempo'] ?? ''),
                    ':precio' => (float) ($item['precio_unitario'] ?? 0),
                ]
            );
        }
        return $ordenId;
    }

    private function str(?string $v): ?string
    {
        if ($v === null || trim($v) === '') {
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
        return $n > 0 ? $n : null;
    }

    private function date(?string $v): ?string
    {
        if ($v === null || $v === '' || !preg_match('/^\d{4}-\d{2}-\d{2}/', trim($v))) {
            return null;
        }
        return substr(trim($v), 0, 10);
    }
}
