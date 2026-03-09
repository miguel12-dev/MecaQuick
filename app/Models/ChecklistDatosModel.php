<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Datos de cabecera del checklist (checklist_datos).
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
            ':numero_orden' => (string) ($datos['numero_orden'] ?? ''),
            ':tipo_comercial_codigo' => $this->str($datos['tipo_comercial_codigo'] ?? null),
            ':matricula' => strtoupper((string) ($datos['matricula'] ?? '')),
            ':matriculacion' => $this->date($datos['matriculacion'] ?? null),
            ':bastidor' => strtoupper((string) ($datos['bastidor'] ?? '')),
            ':ldm' => $this->str($datos['ldm'] ?? null),
            ':djka' => $this->str($datos['djka'] ?? null),
            ':kilometraje' => (int) ($datos['kilometraje'] ?? 0),
            ':asesor' => (string) ($datos['asesor'] ?? ''),
            ':tipo_comercial_modelo' => $this->str($datos['tipo_comercial_modelo'] ?? null),
            ':ldc' => $this->str($datos['ldc'] ?? null),
            ':vhn' => $this->str($datos['vhn'] ?? null),
            ':ano_modelo' => $this->int($datos['ano_modelo'] ?? null),
            ':fecha_servicio' => (string) ($datos['fecha_servicio'] ?? ''),
            ':tipo_inspeccion' => $this->str($datos['tipo_inspeccion'] ?? null),
            ':km_salida' => $this->int($datos['km_salida'] ?? null),
            ':km_llegada' => $this->int($datos['km_llegada'] ?? null),
            ':observaciones' => $this->str($datos['observaciones'] ?? null),
            ':nota_mantenimiento' => $this->str($datos['nota_mantenimiento'] ?? null),
            ':fecha_firma_responsable' => $this->date($datos['fecha_firma_responsable'] ?? null),
            ':fecha_firma_control' => $this->date($datos['fecha_firma_control'] ?? null),
        ];

        if ($existente === null) {
            $this->executeStatement(
                'INSERT INTO checklist_datos (
                    inspeccion_id, numero_orden, tipo_comercial_codigo, matricula, matriculacion,
                    bastidor, ldm, djka, kilometraje, asesor, tipo_comercial_modelo, ldc, vhn,
                    ano_modelo, fecha_servicio, tipo_inspeccion, km_salida, km_llegada,
                    observaciones, nota_mantenimiento, fecha_firma_responsable, fecha_firma_control
                ) VALUES (
                    :inspeccion_id, :numero_orden, :tipo_comercial_codigo, :matricula, :matriculacion,
                    :bastidor, :ldm, :djka, :kilometraje, :asesor, :tipo_comercial_modelo, :ldc, :vhn,
                    :ano_modelo, :fecha_servicio, :tipo_inspeccion, :km_salida, :km_llegada,
                    :observaciones, :nota_mantenimiento, :fecha_firma_responsable, :fecha_firma_control
                )',
                $params
            );
            return;
        }

        $this->executeStatement(
            'UPDATE checklist_datos SET
                numero_orden = :numero_orden,
                tipo_comercial_codigo = :tipo_comercial_codigo,
                matricula = :matricula,
                matriculacion = :matriculacion,
                bastidor = :bastidor,
                ldm = :ldm,
                djka = :djka,
                kilometraje = :kilometraje,
                asesor = :asesor,
                tipo_comercial_modelo = :tipo_comercial_modelo,
                ldc = :ldc,
                vhn = :vhn,
                ano_modelo = :ano_modelo,
                fecha_servicio = :fecha_servicio,
                tipo_inspeccion = :tipo_inspeccion,
                km_salida = :km_salida,
                km_llegada = :km_llegada,
                observaciones = :observaciones,
                nota_mantenimiento = :nota_mantenimiento,
                fecha_firma_responsable = :fecha_firma_responsable,
                fecha_firma_control = :fecha_firma_control,
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

    private function date(?string $v): ?string
    {
        if ($v === null || $v === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($v))) {
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
