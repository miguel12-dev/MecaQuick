<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ChecklistDatosModel;
use App\Models\ConfiguracionModel;
use App\Models\InspeccionAyudantesModel;
use App\Models\InspeccionModel;
use App\Models\OrdenRepuestosItemsModel;
use App\Models\OrdenRepuestosModel;
use App\Services\AuthService;
use Core\BaseController;

/**
 * Módulo orden de repuestos. Acceso exclusivo para rol aprendiz.
 */
final class OrdenRepuestosController extends BaseController
{
    public function index(): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $aprendizId = (int) ($usuario['id'] ?? 0);
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $ordenModel = new OrdenRepuestosModel();
        $ordenes = $ordenModel->listarPendientesPorAprendiz($aprendizId);

        $this->view('OrdenRepuestos.index', [
            'titulo'  => $nombreSistema . ' - Órdenes de repuestos',
            'usuario' => $usuario,
            'ordenes' => $ordenes,
        ]);
    }

    /**
     * Crear o editar orden desde inspección. Si no existe orden, la crea copiando datos del checklist.
     */
    public function crear(?string $inspeccionId = null): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $aprendizId = (int) ($usuario['id'] ?? 0);
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $inspeccionIdInt = (int) ($inspeccionId ?? 0);
        if ($inspeccionIdInt < 1) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $inspeccionModel = new InspeccionModel();
        if (!$inspeccionModel->puedeVerAprendiz($inspeccionIdInt, $aprendizId)) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $ordenModel = new OrdenRepuestosModel();
        $orden = $ordenModel->obtenerPorInspeccionId($inspeccionIdInt);

        if ($orden === null) {
            $checklistModel = new ChecklistDatosModel();
            $cd = $checklistModel->obtenerPorInspeccionId($inspeccionIdInt);
            $datos = $this->mapearChecklistAOrden($cd);
            $datos['fecha_entrada'] = $cd['fecha_ingreso'] ?? date('Y-m-d');
            $datos['hora_entrada'] = $cd['hora_ingreso'] ?? date('H:i');
            $ordenId = $ordenModel->crearDesdeInspeccion($inspeccionIdInt, $datos);
            $orden = $ordenModel->obtenerPorId($ordenId);
        }

        if ($orden === null) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $itemsModel = new OrdenRepuestosItemsModel();
        $items = $itemsModel->listarPorOrdenId((int) $orden['id']);

        $this->view('OrdenRepuestos.crear', [
            'titulo' => $nombreSistema . ' - Orden de repuestos',
            'usuario' => $usuario,
            'orden'   => $orden,
            'items'   => $items,
        ]);
    }

    public function editar(?string $id = null): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $aprendizId = (int) ($usuario['id'] ?? 0);
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $ordenId = (int) ($id ?? 0);
        if ($ordenId < 1) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $ordenModel = new OrdenRepuestosModel();
        if (!$ordenModel->puedeVerAprendiz($ordenId, $aprendizId)) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $orden = $ordenModel->obtenerPorId($ordenId);
        if ($orden === null) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $itemsModel = new OrdenRepuestosItemsModel();
        $items = $itemsModel->listarPorOrdenId($ordenId);

        $this->view('OrdenRepuestos.crear', [
            'titulo' => $nombreSistema . ' - Editar orden de repuestos',
            'usuario' => $usuario,
            'orden'   => $orden,
            'items'   => $items,
        ]);
    }

    public function guardar(): void
    {
        AuthService::requireAprendiz();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $usuario = AuthService::getLoggedUser();
        $aprendizId = (int) ($usuario['id'] ?? 0);
        $ordenId = (int) ($_POST['orden_id'] ?? 0);

        if ($ordenId < 1 || $aprendizId < 1) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $ordenModel = new OrdenRepuestosModel();
        if (!$ordenModel->puedeVerAprendiz($ordenId, $aprendizId)) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $datos = $this->extraerDatosOrden($_POST);
        $items = $this->extraerItems($_POST);

        $total = 0.0;
        foreach ($items as $it) {
            $precio = (float) ($it['precio'] ?? 0);
            $cant = (float) ($it['cant_tiempo'] ?? 1);
            if ($cant <= 0) {
                $cant = 1;
            }
            $total += $precio * $cant;
        }
        $datos['total'] = $total;
        $datos['estado'] = 'completada';

        $ordenModel->actualizar($ordenId, $datos);

        $itemsModel = new OrdenRepuestosItemsModel();
        $itemsModel->guardarItems($ordenId, $items);

        $this->redirect('/orden-repuestos', 302);
    }

    /**
     * @param array<string, mixed>|null $cd
     * @return array<string, mixed>
     */
    private function mapearChecklistAOrden(?array $cd): array
    {
        if ($cd === null) {
            return [];
        }
        return [
            'cliente_nombre'   => $cd['cliente_nombre'] ?? $cd['asesor'] ?? '',
            'cliente_documento' => $cd['cliente_documento'] ?? $cd['tipo_comercial_codigo'] ?? '',
            'cliente_telefono' => $cd['cliente_telefono'] ?? $cd['ldc'] ?? '',
            'cliente_email'    => $cd['cliente_email'] ?? $cd['vhn'] ?? '',
            'placa'            => $cd['placa'] ?? $cd['matricula'] ?? '',
            'modelo'           => $cd['modelo'] ?? $cd['tipo_comercial_modelo'] ?? '',
            'vin'              => $cd['bastidor'] ?? '',
            'mto_km'           => (int) ($cd['kilometraje'] ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function extraerDatosOrden(array $post): array
    {
        return [
            'cliente_nombre'      => trim((string) ($post['cliente_nombre'] ?? '')),
            'cliente_documento'   => trim((string) ($post['cliente_documento'] ?? '')),
            'cliente_direccion'   => trim((string) ($post['cliente_direccion'] ?? '')),
            'cliente_ciudad'      => trim((string) ($post['cliente_ciudad'] ?? '')),
            'cliente_telefono'    => trim((string) ($post['cliente_telefono'] ?? '')),
            'cliente_celular'     => trim((string) ($post['cliente_celular'] ?? '')),
            'cliente_email'       => trim((string) ($post['cliente_email'] ?? '')),
            'vin'                 => trim((string) ($post['vin'] ?? '')),
            'numero_motor'        => trim((string) ($post['numero_motor'] ?? '')),
            'placa'               => trim((string) ($post['placa'] ?? '')),
            'modelo'              => trim((string) ($post['modelo'] ?? '')),
            'color'              => trim((string) ($post['color'] ?? '')),
            'fecha_entrada'      => $this->date($post['fecha_entrada'] ?? null),
            'hora_entrada'       => trim((string) ($post['hora_entrada'] ?? '')),
            'fecha_prometida'    => $this->date($post['fecha_prometida'] ?? null),
            'hora_prometida'     => trim((string) ($post['hora_prometida'] ?? '')),
            'mto_km'             => isset($post['mto_km']) && $post['mto_km'] !== '' ? (int) $post['mto_km'] : null,
            'rep_gral'           => trim((string) ($post['rep_gral'] ?? '')),
            'firma_recepcionista' => trim((string) ($post['firma_recepcionista'] ?? '')),
            'firma_cliente'      => trim((string) ($post['firma_cliente'] ?? '')),
            'cc_recepcionista'   => trim((string) ($post['cc_recepcionista'] ?? '')),
            'cc_cliente'         => trim((string) ($post['cc_cliente'] ?? '')),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extraerItems(array $post): array
    {
        $items = [];
        $refs = $post['items_referencia'] ?? [];
        $descs = $post['items_descripcion'] ?? [];
        $cants = $post['items_cant_tiempo'] ?? [];
        $precios = $post['items_precio'] ?? [];

        if (!is_array($refs)) {
            $refs = [];
        }
        $n = max(
            count($refs),
            count($descs),
            count($cants),
            count($precios)
        );

        for ($i = 0; $i < $n; $i++) {
            $items[] = [
                'referencia'  => $refs[$i] ?? '',
                'descripcion' => $descs[$i] ?? '',
                'cant_tiempo' => $cants[$i] ?? '',
                'precio'      => (float) ($precios[$i] ?? 0),
            ];
        }
        return $items;
    }

    private function date(?string $v): ?string
    {
        if ($v === null || $v === '' || !preg_match('/^\d{4}-\d{2}-\d{2}/', trim((string) $v))) {
            return null;
        }
        return substr(trim($v), 0, 10);
    }
}
