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
                v.modelo
            FROM citas c
            INNER JOIN fechas_disponibles f ON f.id = c.fecha_id
            INNER JOIN vehiculos v ON v.id = c.vehiculo_id
            INNER JOIN clientes cli ON cli.id = v.cliente_id
            WHERE c.token = :token
        SQL;

        return $this->fetchOne($sql, [':token' => $token]);
    }

    /**
     * Lista citas del día actual con datos de cliente y vehículo.
     * Filtra por fecha_id cuya fecha_disponible.fecha = CURDATE().
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarCitasHoy(): array
    {
        $sql = <<<'SQL'
            SELECT
                c.id,
                c.token,
                c.estado,
                c.observaciones_cliente,
                c.created_at,
                f.fecha,
                cli.id AS cliente_id,
                cli.nombre,
                cli.apellido,
                cli.documento,
                cli.telefono,
                cli.email,
                v.id AS vehiculo_id,
                v.placa,
                v.marca,
                v.modelo,
                v.color,
                v.numero_licencia_transito
            FROM citas c
            INNER JOIN fechas_disponibles f ON f.id = c.fecha_id
            INNER JOIN vehiculos v ON v.id = c.vehiculo_id
            INNER JOIN clientes cli ON cli.id = v.cliente_id
            WHERE f.fecha = CURDATE()
              AND c.estado IN ('pendiente', 'confirmada')
            ORDER BY c.created_at ASC
        SQL;

        return $this->fetchAll($sql);
    }

    /**
     * Obtiene una cita por ID con datos de cliente y vehículo (para prellenar formulario de recepción).
     *
     * @return array<string, mixed>|null
     */
    public function obtenerPorIdParaRecepcion(int $citaId): ?array
    {
        $sql = <<<'SQL'
            SELECT
                c.id,
                c.token,
                c.estado,
                c.observaciones_cliente,
                c.created_at,
                f.fecha,
                cli.id AS cliente_id,
                cli.nombre,
                cli.apellido,
                cli.documento,
                cli.telefono,
                cli.email,
                v.id AS vehiculo_id,
                v.placa,
                v.marca,
                v.modelo,
                v.color,
                v.numero_licencia_transito
            FROM citas c
            INNER JOIN fechas_disponibles f ON f.id = c.fecha_id
            INNER JOIN vehiculos v ON v.id = c.vehiculo_id
            INNER JOIN clientes cli ON cli.id = v.cliente_id
            WHERE c.id = :id
        SQL;

        return $this->fetchOne($sql, [':id' => $citaId]);
    }
}

