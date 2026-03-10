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
     * @return array<string, mixed>|null Claves alineadas con el formulario (numero_orden, matricula, etc.)
     */
    public function obtenerPorInspeccionId(int $inspeccionId): ?array
    {
        $row = $this->fetchOne(
            'SELECT numero_orden, tipo_comercial_codigo, matricula, matriculacion, bastidor, ldm, djka,
                    kilometraje, asesor, tipo_comercial_modelo, ldc, vhn, ano_modelo, fecha_servicio,
                    tipo_inspeccion, km_salida, km_llegada, observaciones, nota_mantenimiento,
                    fecha_firma_responsable, fecha_firma_control
             FROM checklist_datos WHERE inspeccion_id = :id LIMIT 1',
            [':id' => $inspeccionId]
        );
        if ($row === null) {
            return null;
        }
        return [
            'numero_orden' => (string) ($row['numero_orden'] ?? ''),
            'tipo_comercial_codigo' => (string) ($row['tipo_comercial_codigo'] ?? ''),
            'matricula' => (string) ($row['matricula'] ?? ''),
            'matriculacion' => isset($row['matriculacion']) && $row['matriculacion'] !== null ? (string) $row['matriculacion'] : '',
            'bastidor' => (string) ($row['bastidor'] ?? ''),
            'ldm' => (string) ($row['ldm'] ?? ''),
            'djka' => (string) ($row['djka'] ?? ''),
            'kilometraje' => (int) ($row['kilometraje'] ?? 0),
            'asesor' => (string) ($row['asesor'] ?? ''),
            'tipo_comercial_modelo' => (string) ($row['tipo_comercial_modelo'] ?? ''),
            'ldc' => (string) ($row['ldc'] ?? ''),
            'vhn' => (string) ($row['vhn'] ?? ''),
            'ano_modelo' => $row['ano_modelo'] !== null ? (string) $row['ano_modelo'] : '',
            'fecha_servicio' => (string) ($row['fecha_servicio'] ?? ''),
            'tipo_inspeccion' => (string) ($row['tipo_inspeccion'] ?? ''),
            'km_salida' => $row['km_salida'] !== null ? (string) $row['km_salida'] : '',
            'km_llegada' => $row['km_llegada'] !== null ? (string) $row['km_llegada'] : '',
            'observaciones' => (string) ($row['observaciones'] ?? ''),
            'nota_mantenimiento' => (string) ($row['nota_mantenimiento'] ?? ''),
            'fecha_firma_responsable' => isset($row['fecha_firma_responsable']) && $row['fecha_firma_responsable'] !== null ? (string) $row['fecha_firma_responsable'] : '',
            'fecha_firma_control' => isset($row['fecha_firma_control']) && $row['fecha_firma_control'] !== null ? (string) $row['fecha_firma_control'] : '',
        ];
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
