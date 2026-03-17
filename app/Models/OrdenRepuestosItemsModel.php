<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Ítems de la orden de repuestos (referencia, descripción, cant/tiempo, precio).
 */
final class OrdenRepuestosItemsModel extends BaseModel
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPorOrdenId(int $ordenId): array
    {
        return $this->fetchAll(
            'SELECT id, orden_id, referencia, descripcion, cant_tiempo, precio
             FROM orden_repuestos_items WHERE orden_id = :id ORDER BY id ASC',
            [':id' => $ordenId]
        );
    }

    public function eliminarPorOrdenId(int $ordenId): void
    {
        $this->executeStatement('DELETE FROM orden_repuestos_items WHERE orden_id = :id', [':id' => $ordenId]);
    }

    /**
     * @param array<int, array{referencia?: string, descripcion?: string, cant_tiempo?: string, precio?: float}> $items
     */
    public function guardarItems(int $ordenId, array $items): void
    {
        $this->eliminarPorOrdenId($ordenId);
        foreach ($items as $item) {
            $ref = trim((string) ($item['referencia'] ?? ''));
            $desc = trim((string) ($item['descripcion'] ?? ''));
            $cant = trim((string) ($item['cant_tiempo'] ?? ''));
            $precio = (float) ($item['precio'] ?? 0);
            if ($ref === '' && $desc === '' && $cant === '' && $precio <= 0) {
                continue;
            }
            $this->executeStatement(
                'INSERT INTO orden_repuestos_items (orden_id, referencia, descripcion, cant_tiempo, precio)
                 VALUES (:orden_id, :referencia, :descripcion, :cant_tiempo, :precio)',
                [
                    ':orden_id' => $ordenId,
                    ':referencia' => $ref ?: null,
                    ':descripcion' => $desc ?: null,
                    ':cant_tiempo' => $cant ?: null,
                    ':precio' => $precio > 0 ? $precio : null,
                ]
            );
        }
    }
}
