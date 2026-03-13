<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\BaseController;
use App\Models\ConfiguracionModel;
use App\Models\UsuarioSistemaModel;
use App\Services\AuthService;
use App\Services\MailService;

/**
 * Gestión de usuarios: solo dos apartados (Gestión Instructores y Gestión Aprendices). Solo admin.
 * Rutas: /usuarios/instructores, /usuarios/aprendices, /usuarios/crear (con ?rol=instructor|aprendiz).
 */
class UsuariosController extends BaseController
{
    /**
     * Redirige a Gestión Instructores.
     */
    public function index(): void
    {
        AuthService::requireAdmin();
        $this->redirect('/usuarios/instructores');
    }

    /**
     * Gestión Instructores: listado y botón para crear instructor.
     */
    public function instructores(): void
    {
        AuthService::requireAdmin();

        $model = new UsuarioSistemaModel();
        $lista = $model->listarPorRol('instructor');
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $this->view('Usuarios.instructores', [
            'titulo'        => $nombreSistema . ' - Gestión Instructores',
            'nombreSistema' => $nombreSistema,
            'usuarios'      => $lista,
        ]);
    }

    /**
     * Gestión Aprendices: listado y botón para crear aprendiz.
     */
    public function aprendices(): void
    {
        AuthService::requireAdmin();

        $model = new UsuarioSistemaModel();
        $lista = $model->listarPorRol('aprendiz');
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $this->view('Usuarios.aprendices', [
            'titulo'        => $nombreSistema . ' - Gestión Aprendices',
            'nombreSistema' => $nombreSistema,
            'usuarios'      => $lista,
        ]);
    }

    /**
     * GET: formulario crear (rol fijo por ?rol=instructor|aprendiz). POST: crear y enviar credenciales por correo.
     */
    public function crear(): void
    {
        AuthService::requireAdmin();

        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';
        $rol = isset($_GET['rol']) && $_GET['rol'] === 'instructor' ? 'instructor' : (isset($_GET['rol']) && $_GET['rol'] === 'aprendiz' ? 'aprendiz' : null);

        if ($rol === null && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            $this->redirect('/usuarios/instructores');
            return;
        }

        if (($rol ?? '') === '') {
            $rol = trim((string) ($_POST['rol'] ?? ''));
            if (!in_array($rol, ['instructor', 'aprendiz'], true)) {
                $rol = 'aprendiz';
            }
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->procesarCrear($nombreSistema, $rol);
            return;
        }

        $tituloCrear = $rol === 'instructor' ? 'Nuevo instructor' : 'Nuevo aprendiz';
        $this->view('Usuarios.crear', [
            'titulo'        => $nombreSistema . ' - ' . $tituloCrear,
            'nombreSistema' => $nombreSistema,
            'rol'           => $rol,
            'error'         => null,
            'old'           => ['nombre' => '', 'email' => ''],
        ]);
    }

    private function procesarCrear(string $nombreSistema, string $rol): void
    {
        $nombre   = trim((string) ($_POST['nombre'] ?? ''));
        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $errores = [];
        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }
        if ($email === '') {
            $errores[] = 'El correo es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo no tiene un formato válido.';
        }
        if (strlen($password) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        }

        if ($errores !== []) {
            $this->view('Usuarios.crear', [
                'titulo'        => $nombreSistema . ' - ' . ($rol === 'instructor' ? 'Nuevo instructor' : 'Nuevo aprendiz'),
                'nombreSistema' => $nombreSistema,
                'rol'           => $rol,
                'error'         => implode(' ', $errores),
                'old'           => ['nombre' => $nombre, 'email' => $email],
            ]);
            return;
        }

        $model = new UsuarioSistemaModel();
        try {
            $model->crear($nombre, $email, $password, $rol);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), '1062')) {
                $this->view('Usuarios.crear', [
                    'titulo'        => $nombreSistema . ' - ' . ($rol === 'instructor' ? 'Nuevo instructor' : 'Nuevo aprendiz'),
                    'nombreSistema' => $nombreSistema,
                    'rol'           => $rol,
                    'error'         => 'Ya existe un usuario con ese correo.',
                    'old'           => ['nombre' => $nombre, 'email' => $email],
                ]);
                return;
            }
            throw $e;
        }

        $mailService = new MailService();
        $mailService->enviarCredencialesUsuario($email, $nombre, $email, $password, $rol);

        $rutaListado = $rol === 'instructor' ? '/usuarios/instructores' : '/usuarios/aprendices';
        $this->redirect($rutaListado);
    }

    /**
     * GET: formulario editar usuario. POST: actualizar datos.
     */
    public function editar(string $id): void
    {
        AuthService::requireAdmin();

        $idUsuario = (int) $id;
        if ($idUsuario <= 0) {
            $this->redirect('/usuarios/instructores');
            return;
        }

        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';
        $model = new UsuarioSistemaModel();
        $usuario = $model->obtenerParaEdicion($idUsuario);

        if ($usuario === null) {
            $this->redirect('/usuarios/instructores');
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->procesarEditar($idUsuario, $usuario['rol'], $nombreSistema);
            return;
        }

        $tituloEditar = $usuario['rol'] === 'instructor' ? 'Editar instructor' : 'Editar aprendiz';
        $this->view('Usuarios.editar', [
            'titulo'        => $nombreSistema . ' - ' . $tituloEditar,
            'nombreSistema' => $nombreSistema,
            'usuario'       => $usuario,
            'error'         => null,
        ]);
    }

    private function procesarEditar(int $id, string $rol, string $nombreSistema): void
    {
        $nombre  = trim((string) ($_POST['nombre'] ?? ''));
        $email   = trim((string) ($_POST['email'] ?? ''));
        $activo  = isset($_POST['activo']) && $_POST['activo'] === '1' ? 1 : 0;

        $errores = [];
        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }
        if ($email === '') {
            $errores[] = 'El correo es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo no tiene un formato válido.';
        }

        if ($errores !== []) {
            $this->view('Usuarios.editar', [
                'titulo'        => $nombreSistema . ' - ' . ($rol === 'instructor' ? 'Editar instructor' : 'Editar aprendiz'),
                'nombreSistema' => $nombreSistema,
                'usuario'       => ['id' => $id, 'nombre' => $nombre, 'email' => $email, 'rol' => $rol, 'activo' => $activo],
                'error'         => implode(' ', $errores),
            ]);
            return;
        }

        $model = new UsuarioSistemaModel();
        try {
            $model->actualizar($id, $nombre, $email, $activo);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), '1062')) {
                $this->view('Usuarios.editar', [
                    'titulo'        => $nombreSistema . ' - ' . ($rol === 'instructor' ? 'Editar instructor' : 'Editar aprendiz'),
                    'nombreSistema' => $nombreSistema,
                    'usuario'       => ['id' => $id, 'nombre' => $nombre, 'email' => $email, 'rol' => $rol, 'activo' => $activo],
                    'error'         => 'Ya existe un usuario con ese correo.',
                ]);
                return;
            }
            throw $e;
        }

        $rutaListado = $rol === 'instructor' ? '/usuarios/instructores' : '/usuarios/aprendices';
        $this->redirect($rutaListado);
    }

    /**
     * Elimina un usuario (soft delete).
     */
    public function eliminar(string $id): void
    {
        AuthService::requireAdmin();

        $idUsuario = (int) $id;
        if ($idUsuario <= 0) {
            $this->redirect('/usuarios/instructores');
            return;
        }

        $model = new UsuarioSistemaModel();
        $usuario = $model->obtenerParaEdicion($idUsuario);

        if ($usuario === null) {
            $this->redirect('/usuarios/instructores');
            return;
        }

        $model->eliminar($idUsuario);

        $rutaListado = $usuario['rol'] === 'instructor' ? '/usuarios/instructores' : '/usuarios/aprendices';
        $this->redirect($rutaListado);
    }
}
