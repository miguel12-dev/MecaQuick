<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

final class VehiculoModel extends BaseModel
{
    public function obtenerPorPlaca(string $placa): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM vehiculos WHERE placa = :placa',
            [':placa' => $placa]
        );
    }

    /**
     * v1.0: tipo_vehiculo_id por defecto 1 (automovil).
     */
    public function crear(
        string $placa,
        string $marca,
        string $modelo,
        ?int $anio,
        int $clienteId
    ): int {
        $this->executeStatement(
            'INSERT INTO vehiculos (placa, marca, modelo, anio, cliente_id, tipo_vehiculo_id)
             VALUES (:placa, :marca, :modelo, :anio, :cliente_id, 1)',
            [
                ':placa'      => $placa,
                ':marca'      => $marca,
                ':modelo'     => $modelo,
                ':anio'       => $anio,
                ':cliente_id' => $clienteId,
            ]
        );

        return (int) $this->lastInsertId();
    }

    public function obtenerIdOCrear(
        string $placa,
        string $marca,
        string $modelo,
        ?int $anio,
        int $clienteId
    ): int {
        $existente = $this->obtenerPorPlaca($placa);

        if ($existente !== null) {
            return (int) $existente['id'];
        }

        return $this->crear($placa, $marca, $modelo, $anio, $clienteId);
    }

    /**
     * Actualiza datos de recepción. v1.0: vehiculos no tiene kilometraje ni fecha_venta.
     * Kilometraje se guarda en recepciones_orden_trabajo.kilometros_entrada.
     */
    public function actualizarDatosRecepcion(
        int $id,
        ?string $vin,
        ?string $numeroMotor,
        ?int $kilometraje,
        ?string $fechaVenta
    ): void {
        $this->executeStatement(
            'UPDATE vehiculos SET vin = :vin, numero_motor = :numero_motor WHERE id = :id',
            [
                ':vin' => $vin,
                ':numero_motor' => $numeroMotor,
                ':id' => $id,
            ]
        );
    }
}

