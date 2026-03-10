<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

final class CitaModel extends BaseModel
{
    public function crear(
        string $token,
        int $fechaId,
        int $vehiculoId,
        ?string $observaciones
    ): int {
        $this->executeStatement(
            'INSERT INTO citas (token, fecha_id, vehiculo_id, observaciones_cliente)
             VALUES (:token, :fecha_id, :vehiculo_id, :observaciones)',
            [
                ':token'         => $token,
                ':fecha_id'      => $fechaId,
                ':vehiculo_id'   => $vehiculoId,
                ':observaciones' => $observaciones,
            ]
        );

        return (int) $this->lastInsertId();
    }

    public function marcarCorreoEnviado(int $citaId): void
    {
        $this->executeStatement(
            'UPDATE citas SET correo_enviado = 1 WHERE id = :id',
            [':id' => $citaId]
        );
    }

    /**
     * Citas del día actual (para recepción).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarCitasHoy(): array
    {
        $sql = <<<'SQL'
            SELECT
                c.id AS cita_id,
                c.token,
                c.estado,
                c.observaciones_cliente,
                f.fecha,
                cli.id AS cliente_id,
                cli.nombre,
                cli.apellido,
                cli.documento,
                cli.telefono,
                cli.email,
                cli.direccion,
                cli.ciudad,
                v.id AS vehiculo_id,
                v.placa,
                v.marca,
                v.modelo,
                v.anio,
                v.color,
                v.vin,
                v.numero_motor,
                v.kilometraje,
                v.fecha_venta
            FROM citas c
            INNER JOIN fechas_disponibles f ON f.id = c.fecha_id AND f.fecha = CURDATE()
            INNER JOIN vehiculos v ON v.id = c.vehiculo_id
            INNER JOIN clientes cli ON cli.id = v.cliente_id
            WHERE c.estado IN ('pendiente','confirmada','reagendada')
            ORDER BY c.id ASC
        SQL;

        return $this->fetchAll($sql);
    }

    /**
     * Cita completa con cliente y vehículo (para recepción).
     */
    public function obtenerCompletaPorId(int $citaId): ?array
    {
        $sql = <<<'SQL'
            SELECT
                c.id AS cita_id,
                c.token,
                c.estado,
                c.observaciones_cliente,
                f.fecha,
                cli.id AS cliente_id,
                cli.nombre,
                cli.apellido,
                cli.documento,
                cli.telefono,
                cli.email,
                cli.direccion,
                cli.ciudad,
                v.id AS vehiculo_id,
                v.placa,
                v.marca,
                v.modelo,
                v.anio,
                v.color,
                v.vin,
                v.numero_motor,
                v.kilometraje,
                v.fecha_venta
            FROM citas c
            INNER JOIN fechas_disponibles f ON f.id = c.fecha_id
            INNER JOIN vehiculos v ON v.id = c.vehiculo_id
            INNER JOIN clientes cli ON cli.id = v.cliente_id
            WHERE c.id = :id
        SQL;

        return $this->fetchOne($sql, [':id' => $citaId]);
    }

    public function obtenerPorToken(string $token): ?array
    {
        $sql = <<<'SQL'
            SELECT
                c.id,
                c.token,
                c.estado,
                c.observaciones_cliente,
                c.created_at,
                f.fecha,
                f.max_cupos,
                cli.nombre,
                cli.apellido,
                cli.documento,
                cli.telefono,
                cli.email,
                v.placa,
                v.marca,
                v.modelo,
                v.anio
            FROM citas c
            INNER JOIN fechas_disponibles f ON f.id = c.fecha_id
            INNER JOIN vehiculos v ON v.id = c.vehiculo_id
            INNER JOIN clientes cli ON cli.id = v.cliente_id
            WHERE c.token = :token
        SQL;

        return $this->fetchOne($sql, [':token' => $token]);
    }
}

