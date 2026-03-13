<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\BaseController;
use App\Models\ConfiguracionModel;
use App\Models\UsuarioSistemaModel;
use App\Services\AuthService;

/**
 * Gestión de cuenta de usuario.
 * Permite edición de datos personales y cambio de contraseña.
 */
class CuentaController extends BaseController
{
    public function index(): void
    {
        AuthService::requireAuth();

        $usuario = AuthService::getLoggedUser();
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $this->view('Cuenta.index', [
            'titulo'        => $nombreSistema . ' - Mi Cuenta',
            'nombreSistema' => $nombreSistema,
            'usuario'       => $usuario,
        ]);
    }

    public function actualizar(): void
    {
        AuthService::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cuenta');
            exit;
        }

        $usuario = AuthService::getLoggedUser();
        $usuarioModel = new UsuarioSistemaModel();

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if (empty($nombre) || empty($correo)) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'Todos los campos son obligatorios.'
            ];
            header('Location: /cuenta');
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'El correo electrónico no es válido.'
            ];
            header('Location: /cuenta');
            exit;
        }

        $actualizado = $usuarioModel->actualizarDatos(
            (int)$usuario['id'],
            $nombre,
            $correo
        );

        if ($actualizado) {
            $_SESSION['mecaquick_usuario']['nombre'] = $nombre;
            $_SESSION['mecaquick_usuario']['email'] = $correo;

            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Datos actualizados correctamente.'
            ];
        } else {
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'Error al actualizar los datos.'
            ];
        }

        header('Location: /cuenta');
        exit;
    }

    public function cambiarPassword(): void
    {
        AuthService::requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cuenta');
            exit;
        }

        $usuario = AuthService::getLoggedUser();
        $usuarioModel = new UsuarioSistemaModel();

        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirmar = $_POST['password_confirmar'] ?? '';

        if (empty($passwordActual) || empty($passwordNueva) || empty($passwordConfirmar)) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'Todos los campos son obligatorios.'
            ];
            header('Location: /cuenta');
            exit;
        }

        if ($passwordNueva !== $passwordConfirmar) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'Las contraseñas nuevas no coinciden.'
            ];
            header('Location: /cuenta');
            exit;
        }

        if (strlen($passwordNueva) < 6) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'La contraseña debe tener al menos 6 caracteres.'
            ];
            header('Location: /cuenta');
            exit;
        }

        $usuarioData = $usuarioModel->findByIdCompleto((int)$usuario['id']);

        if (!$usuarioData || !password_verify($passwordActual, $usuarioData['password_hash'])) {
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'La contraseña actual es incorrecta.'
            ];
            header('Location: /cuenta');
            exit;
        }

        $actualizado = $usuarioModel->cambiarPassword(
            (int)$usuario['id'],
            password_hash($passwordNueva, PASSWORD_BCRYPT)
        );

        if ($actualizado) {
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Contraseña cambiada correctamente.'
            ];
        } else {
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'Error al cambiar la contraseña.'
            ];
        }

        header('Location: /cuenta');
        exit;
    }
}
