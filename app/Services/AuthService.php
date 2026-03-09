<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UsuarioSistemaModel;

/**
 * Servicio de autenticación para usuarios del sistema (admin, instructor, aprendiz).
 * Gestiona login, sesión y protección de rutas. El registro no es público.
 */
final class AuthService
{
    private const SESSION_KEY = 'mecaquick_usuario';

    private static ?UsuarioSistemaModel $usuarioModel = null;

    private static function model(): UsuarioSistemaModel
    {
        if (self::$usuarioModel === null) {
            self::$usuarioModel = new UsuarioSistemaModel();
        }
        return self::$usuarioModel;
    }

    /**
     * Intenta login con email y contraseña. Devuelve datos del usuario o null.
     *
     * @return array{id: int, nombre: string, email: string, rol: string}|null
     */
    public static function login(string $email, string $password): ?array
    {
        $email = trim($email);
        if ($email === '' || $password === '') {
            return null;
        }

        $usuario = self::model()->findByEmail($email);
        if ($usuario === null || !password_verify($password, $usuario['password_hash'])) {
            return null;
        }

        $sessionData = [
            'id'     => (int) $usuario['id'],
            'nombre' => $usuario['nombre'],
            'email'  => $usuario['email'],
            'rol'    => $usuario['rol'],
        ];

        $_SESSION[self::SESSION_KEY] = $sessionData;
        return $sessionData;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Devuelve el usuario en sesión o null.
     *
     * @return array{id: int, nombre: string, email: string, rol: string}|null
     */
    public static function getLoggedUser(): ?array
    {
        $data = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_array($data) || !isset($data['id'], $data['nombre'], $data['rol'])) {
            return null;
        }
        return $data;
    }

    public static function isLoggedIn(): bool
    {
        return self::getLoggedUser() !== null;
    }

    /**
     * Redirige a /login si no hay sesión. Usar al inicio de acciones que requieren auth.
     */
    public static function requireAuth(): void
    {
        if (self::isLoggedIn()) {
            return;
        }
        header('Location: /login', true, 302);
        exit;
    }

    /**
     * Exige sesión y rol admin. Redirige a /dashboard si no es admin.
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        $user = self::getLoggedUser();
        if ($user === null || $user['rol'] !== 'admin') {
            header('Location: /dashboard', true, 302);
            exit;
        }
    }

    /**
     * Exige sesión y rol instructor o admin. Redirige a /dashboard si no cumple.
     */
    public static function requireInstructor(): void
    {
        self::requireAuth();
        $user = self::getLoggedUser();
        if ($user === null || !in_array($user['rol'], ['instructor', 'admin'], true)) {
            header('Location: /dashboard', true, 302);
            exit;
        }
    }

    /**
     * Exige sesión y rol aprendiz. Redirige a /dashboard si no cumple.
     */
    public static function requireAprendiz(): void
    {
        self::requireAuth();
        $user = self::getLoggedUser();
        if ($user === null || $user['rol'] !== 'aprendiz') {
            header('Location: /dashboard', true, 302);
            exit;
        }
    }
}
