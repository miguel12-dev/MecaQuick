<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

final class ClienteModel extends BaseModel
{
    public function obtenerPorDocumento(string $documento): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM clientes WHERE documento = :documento',
            [':documento' => $documento]
        );
    }

    /**
     * v1.0: columnas documento, nombre, apellido, email, telefono, celular, direccion, ciudad.
     */
    public function crear(
        string $nombre,
        string $apellido,
        string $documento,
        string $telefono,
        string $email
    ): int {
        $this->executeStatement(
            'INSERT INTO clientes (documento, nombre, apellido, email, telefono)
             VALUES (:documento, :nombre, :apellido, :email, :telefono)',
            [
                ':documento' => $documento,
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':email'     => $email,
                ':telefono'  => $telefono,
            ]
        );

        return (int) $this->lastInsertId();
    }

    public function obtenerIdOCrear(
        string $nombre,
        string $apellido,
        string $documento,
        string $telefono,
        string $email
    ): int {
        $existente = $this->obtenerPorDocumento($documento);

        if ($existente !== null) {
            return (int) $existente['id'];
        }

        return $this->crear($nombre, $apellido, $documento, $telefono, $email);
    }

    public function actualizarDireccionCiudad(int $id, ?string $direccion, ?string $ciudad): void
    {
        $this->executeStatement(
            'UPDATE clientes SET direccion = :direccion, ciudad = :ciudad WHERE id = :id',
            [':direccion' => $direccion, ':ciudad' => $ciudad, ':id' => $id]
        );
    }
}

