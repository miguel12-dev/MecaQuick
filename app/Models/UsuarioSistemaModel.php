<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Acceso a usuarios del sistema (tabla usuarios_sistema).
 * Roles: admin, instructor, aprendiz. El registro se realiza desde la aplicación, no desde formulario público.
 */
final class UsuarioSistemaModel extends BaseModel
{
    private const TABLE = 'usuarios_sistema';

    /**
     * Busca un usuario por email. Solo devuelve activos.
     *
     * @return array{id: int, nombre: string, email: string, password_hash: string, rol: string, activo: int}|null
     */
    public function findByEmail(string $email): ?array
    {
        $fila = $this->fetchOne(
            'SELECT id, nombre, email, password_hash, rol, activo FROM ' . self::TABLE .
            ' WHERE email = :email AND activo = 1 LIMIT 1',
            [':email' => $email]
        );

        if ($fila === null) {
            return null;
        }

        $fila['id'] = (int) $fila['id'];
        $fila['activo'] = (int) $fila['activo'];
        return $fila;
    }

    /**
     * Obtiene un usuario por ID (para sesión).
     *
     * @return array{id: int, nombre: string, email: string, rol: string}|null
     */
    public function findById(int $id): ?array
    {
        $fila = $this->fetchOne(
            'SELECT id, nombre, email, rol FROM ' . self::TABLE . ' WHERE id = :id AND activo = 1 LIMIT 1',
            [':id' => $id]
        );

        if ($fila === null) {
            return null;
        }

        $fila['id'] = (int) $fila['id'];
        return $fila;
    }

    /**
     * Lista usuarios por rol (admin, instructor, aprendiz). Incluye inactivos para gestión.
     *
     * @return array<int, array{id: int, nombre: string, email: string, rol: string, activo: int, created_at: string}>
     */
    public function listarPorRol(string $rol): array
    {
        $rolesPermitidos = ['admin', 'instructor', 'aprendiz'];
        if (!in_array($rol, $rolesPermitidos, true)) {
            return [];
        }

        $filas = $this->fetchAll(
            'SELECT id, nombre, email, rol, activo, created_at FROM ' . self::TABLE .
            ' WHERE rol = :rol ORDER BY nombre ASC',
            [':rol' => $rol]
        );

        foreach ($filas as &$fila) {
            $fila['id'] = (int) $fila['id'];
            $fila['activo'] = (int) $fila['activo'];
        }
        return $filas;
    }

    /**
     * Crea un usuario desde la aplicación (admin/script). No hay registro público.
     * Rol: admin | instructor | aprendiz.
     */
    public function crear(string $nombre, string $email, string $passwordPlano, string $rol): int
    {
        $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);
        $this->executeStatement(
            'INSERT INTO ' . self::TABLE . ' (nombre, email, password_hash, rol) VALUES (:nombre, :email, :hash, :rol)',
            [
                ':nombre' => $nombre,
                ':email'  => $email,
                ':hash'   => $hash,
                ':rol'    => $rol,
            ]
        );
        return (int) $this->db->lastInsertId();
    }
}
