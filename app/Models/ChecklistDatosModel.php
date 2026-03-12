<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Datos del checklist técnico de vehículos (checklist_datos).
 */
final class ChecklistDatosModel extends BaseModel
{
    /**
     * Obtiene los datos de cabecera por inspección (para precargar el formulario).
     *
     * @return array<string, mixed>|null Claves: nombre_cliente, cedula_nit, placa, kilometraje, etc.
     */
    public function obtenerPorInspeccionId(int $inspeccionId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM checklist_datos WHERE inspeccion_id = :id LIMIT 1',
            [':id' => $inspeccionId]
        );
    }

    /**
     * @param array<string, mixed> $datos
     */
    public function guardarOActualizar(int $inspeccionId, array $datos): void
    {
        $existente = $this->fetchOne(
            'SELECT id FROM checklist_datos WHERE inspeccion_id = :id',
            [':id' => $inspeccionId]
        );

        $params = [
            ':inspeccion_id' => $inspeccionId,
            ':nombre_cliente' => (string) ($datos['nombre_cliente'] ?? ''),
            ':cedula_nit' => (string) ($datos['cedula_nit'] ?? ''),
            ':telefono' => $this->str($datos['telefono'] ?? null),
            ':correo' => (string) ($datos['correo'] ?? ''),
            ':modelo_vehiculo' => (string) ($datos['modelo_vehiculo'] ?? ''),
            ':placa' => strtoupper((string) ($datos['placa'] ?? '')),
            ':kilometraje' => (int) ($datos['kilometraje'] ?? 0),
            ':fecha_ingreso' => (string) ($datos['fecha_ingreso'] ?? ''),
            ':hora_ingreso' => (string) ($datos['hora_ingreso'] ?? ''),
            ':observaciones_generales' => $this->str($datos['observaciones_generales'] ?? null),
            ':firma_tecnico' => $this->str($datos['firma_tecnico'] ?? null),
            ':nombre_tecnico' => (string) ($datos['nombre_tecnico'] ?? ''),
            ':firma_cliente' => $this->str($datos['firma_cliente'] ?? null),
        ];

        if ($existente === null) {
            $this->executeStatement(
                'INSERT INTO checklist_datos (
                    inspeccion_id, nombre_cliente, cedula_nit, telefono, correo,
                    modelo_vehiculo, placa, kilometraje, fecha_ingreso, hora_ingreso,
                    observaciones_generales, firma_tecnico, nombre_tecnico, firma_cliente
                ) VALUES (
                    :inspeccion_id, :nombre_cliente, :cedula_nit, :telefono, :correo,
                    :modelo_vehiculo, :placa, :kilometraje, :fecha_ingreso, :hora_ingreso,
                    :observaciones_generales, :firma_tecnico, :nombre_tecnico, :firma_cliente
                )',
                $params
            );
            return;
        }

        $this->executeStatement(
            'UPDATE checklist_datos SET
                nombre_cliente = :nombre_cliente,
                cedula_nit = :cedula_nit,
                telefono = :telefono,
                correo = :correo,
                modelo_vehiculo = :modelo_vehiculo,
                placa = :placa,
                kilometraje = :kilometraje,
                fecha_ingreso = :fecha_ingreso,
                hora_ingreso = :hora_ingreso,
                observaciones_generales = :observaciones_generales,
                firma_tecnico = :firma_tecnico,
                nombre_tecnico = :nombre_tecnico,
                firma_cliente = :firma_cliente,
                updated_at = CURRENT_TIMESTAMP
             WHERE inspeccion_id = :inspeccion_id',
            $params
        );
    }

    private function str(?string $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        return trim($v);
    }
}
