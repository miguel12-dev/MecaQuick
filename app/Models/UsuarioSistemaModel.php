<?php

declare(strict_types=1);

namespace App\Models;

use Core\BaseModel;

/**
 * Acceso a usuarios del sistema (tabla usuarios_sistema).
 * v1.0: rol_id FK a roles_usuario. Roles: admin, instructor, aprendiz, asesor_servicio.
 */
final class UsuarioSistemaModel extends BaseModel
{
    private const TABLE = 'usuarios_sistema';

    /**
     * Busca un usuario por email. Solo devuelve activos.
     * Incluye rol (nombre) desde roles_usuario.
     *
     * @return array{id: int, nombre: string, email: string, password_hash: string, rol: string, activo: int}|null
     */
    public function findByEmail(string $email): ?array
    {
        $fila = $this->fetchOne(
            'SELECT u.id, u.nombre, u.email, u.password_hash, u.activo, r.nombre AS rol
             FROM ' . self::TABLE . ' u
             INNER JOIN roles_usuario r ON r.id = u.rol_id
             WHERE u.email = :email AND u.activo = 1 LIMIT 1',
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
            'SELECT u.id, u.nombre, u.email, r.nombre AS rol
             FROM ' . self::TABLE . ' u
             INNER JOIN roles_usuario r ON r.id = u.rol_id
             WHERE u.id = :id AND u.activo = 1 LIMIT 1',
            [':id' => $id]
        );

        if ($fila === null) {
            return null;
        }

        $fila['id'] = (int) $fila['id'];
        return $fila;
    }

    /**
     * Obtiene rol_id por nombre de rol.
     */
    public function obtenerRolIdPorNombre(string $rol): ?int
    {
        $fila = $this->fetchOne(
            'SELECT id FROM roles_usuario WHERE nombre = :rol LIMIT 1',
            [':rol' => $rol]
        );
        return $fila !== null ? (int) $fila['id'] : null;
    }

    /**
     * Lista usuarios por rol (admin, instructor, aprendiz).
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
            'SELECT u.id, u.nombre, u.email, u.activo, u.created_at, r.nombre AS rol
             FROM ' . self::TABLE . ' u
             INNER JOIN roles_usuario r ON r.id = u.rol_id
             WHERE r.nombre = :rol ORDER BY u.nombre ASC',
            [':rol' => $rol]
        );

        foreach ($filas as &$fila) {
            $fila['id'] = (int) $fila['id'];
            $fila['activo'] = (int) $fila['activo'];
        }
        return $filas;
    }

    /**
     * Crea un usuario desde la aplicación (admin/script).
     * Rol: admin | instructor | aprendiz.
     */
    public function crear(string $nombre, string $email, string $passwordPlano, string $rol): int
    {
        $rolId = $this->obtenerRolIdPorNombre($rol);
        if ($rolId === null) {
            throw new \InvalidArgumentException("Rol inválido: $rol");
        }

        $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);
        $this->executeStatement(
            'INSERT INTO ' . self::TABLE . ' (nombre, email, password_hash, rol_id) VALUES (:nombre, :email, :hash, :rol_id)',
            [
                ':nombre' => $nombre,
                ':email'  => $email,
                ':hash'   => $hash,
                ':rol_id' => $rolId,
            ]
        );
        return (int) $this->db->lastInsertId();
    }
}
