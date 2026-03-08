<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\BaseController;
use App\Models\ConfiguracionModel;
use App\Services\AuthService;

/**
 * Controlador de inicio de sesión. Ruta: /login.
 * No hay registro público; los usuarios se crean desde la aplicación.
 */
class LoginController extends BaseController
{
    /**
     * GET: muestra formulario de login.
     * POST: procesa credenciales y redirige a /dashboard o muestra error.
     */
    public function index(): void
    {
        if (AuthService::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->procesarLogin($nombreSistema);
            return;
        }

        $this->view('Auth.login', [
            'titulo'        => $nombreSistema . ' - Iniciar sesión',
            'nombreSistema' => $nombreSistema,
            'error'         => null,
            'email'         => '',
        ]);
    }

    private function procesarLogin(string $nombreSistema): void
    {
        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->view('Auth.login', [
                'titulo'        => $nombreSistema . ' - Iniciar sesión',
                'nombreSistema' => $nombreSistema,
                'error'         => 'Correo y contraseña son obligatorios.',
                'email'         => $email,
            ]);
            return;
        }

        $usuario = AuthService::login($email, $password);

        if ($usuario !== null) {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('Auth.login', [
            'titulo'        => $nombreSistema . ' - Iniciar sesión',
            'nombreSistema' => $nombreSistema,
            'error'         => 'Credenciales incorrectas o usuario inactivo.',
            'email'         => $email,
        ]);
    }
}
