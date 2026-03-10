<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\BaseController;
use App\Services\AuthService;

/**
 * Cierre de sesión. Ruta: /logout.
 */
class LogoutController extends BaseController
{
    public function index(): void
    {
        AuthService::logout();
        $this->redirect('/');
    }
}
