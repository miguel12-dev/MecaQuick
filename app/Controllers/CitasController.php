<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CitaModel;
use App\Models\ConfiguracionModel;
use App\Services\AuthService;
use Core\BaseController;

/**
 * Gestión de citas. Solo admin. Sin filtro de fecha.
 */
final class CitasController extends BaseController
{
    /**
     * Lista todas las citas (admin).
     */
    public function index(): void
    {
        AuthService::requireAdmin();

        $model = new CitaModel();
        $citas = $model->listarTodas();
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $this->view('Citas.index', [
            'titulo' => $nombreSistema . ' - Todas las citas',
            'nombreSistema' => $nombreSistema,
            'citas' => $citas,
        ]);
    }
}
