<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\BaseController;
use App\Models\ConfiguracionModel;
use App\Services\AuthService;

/**
 * Área de cuenta del usuario autenticado. Ruta: /cuenta.
 */
final class CuentaController extends BaseController
{
    public function index(): void
    {
        AuthService::requireAuth();

        $usuario = AuthService::getLoggedUser();
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $this->view('Cuenta.index', [
            'titulo'        => $nombreSistema . ' - Mi cuenta',
            'nombreSistema' => $nombreSistema,
            'usuario'       => $usuario,
        ]);
    }
}