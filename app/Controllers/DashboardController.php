<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\BaseController;
use App\Models\ConfiguracionModel;
use App\Services\AuthService;

/**
 * Panel principal tras iniciar sesión. Ruta: /dashboard.
 */
class DashboardController extends BaseController
{
    public function index(): void
    {
        AuthService::requireAuth();

        $usuario = AuthService::getLoggedUser();
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $this->view('Dashboard.index', [
            'titulo'        => $nombreSistema . ' - Panel',
            'nombreSistema' => $nombreSistema,
            'usuario'       => $usuario,
        ]);
    }
}
