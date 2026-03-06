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

    public function crear(
        string $nombre,
        string $apellido,
        string $documento,
        string $telefono,
        string $email
    ): int {
        $this->executeStatement(
            'INSERT INTO clientes (nombre, apellido, documento, telefono, email)
             VALUES (:nombre, :apellido, :documento, :telefono, :email)',
            [
                ':nombre'    => $nombre,
                ':apellido'  => $apellido,
                ':documento' => $documento,
                ':telefono'  => $telefono,
                ':email'     => $email,
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
}

