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

    public function crear(
        string $placa,
        string $marca,
        string $modelo,
        ?int $anio,
        int $clienteId
    ): int {
        $this->executeStatement(
            'INSERT INTO vehiculos (placa, marca, modelo, anio, cliente_id)
             VALUES (:placa, :marca, :modelo, :anio, :cliente_id)',
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

    public function actualizarDatosRecepcion(
        int $id,
        ?string $vin,
        ?string $numeroMotor,
        ?int $kilometraje,
        ?string $fechaVenta
    ): void {
        $this->executeStatement(
            'UPDATE vehiculos SET vin = :vin, numero_motor = :numero_motor, kilometraje = :kilometraje, fecha_venta = :fecha_venta WHERE id = :id',
            [
                ':vin' => $vin,
                ':numero_motor' => $numeroMotor,
                ':kilometraje' => $kilometraje,
                ':fecha_venta' => $fechaVenta,
                ':id' => $id,
            ]
        );
    }
}

