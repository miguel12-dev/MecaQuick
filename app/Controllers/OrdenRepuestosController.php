<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ChecklistDatosModel;
use App\Models\ConfiguracionModel;
use App\Models\InspeccionModel;
use App\Models\OrdenRepuestosModel;
use App\Services\AuthService;
use Core\BaseController;

/**
 * Módulo de orden de repuestos. Acceso para rol aprendiz.
 */
final class OrdenRepuestosController extends BaseController
{
    public function index(): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $aprendizId = (int) ($usuario['id'] ?? 0);
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $model = new OrdenRepuestosModel();
        $ordenes = $aprendizId > 0 ? $model->listarPorAprendiz($aprendizId) : [];

        $this->view('OrdenRepuestos.index', [
            'titulo'  => $nombreSistema . ' - Orden de repuestos',
            'usuario' => $usuario,
            'ordenes' => $ordenes,
        ]);
    }

    /**
     * Formulario de creación. Con inspeccion_id precarga datos del checklist.
     */
    public function crear(?string $inspeccionId = null): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $datos = [];
        $inspeccionIdInt = (int) ($inspeccionId ?? 0);
        if ($inspeccionIdInt > 0) {
            $checklistModel = new ChecklistDatosModel();
            $cd = $checklistModel->obtenerPorInspeccionId($inspeccionIdInt);
            if ($cd !== null) {
                $datos = $this->mapearChecklistAOrden($cd);
                $datos['inspeccion_id'] = $inspeccionIdInt;
            }
        }

        $this->view('OrdenRepuestos.crear', [
            'titulo'        => $nombreSistema . ' - Nueva orden de repuestos',
            'usuario'       => $usuario,
            'datos'         => $datos,
            'inspeccion_id' => $inspeccionIdInt,
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
        if ($aprendizId < 1) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $datos = $this->extraerDatos($_POST);
        $items = $this->extraerItems($_POST);
        $errores = $this->validar($datos, $items);
        if ($errores !== []) {
            $_SESSION['orden_repuestos_errores'] = $errores;
            $_SESSION['orden_repuestos_datos'] = array_merge($datos, ['items' => $items]);
            $inspeccionId = (int) ($_POST['inspeccion_id'] ?? 0);
            $this->redirect('/orden-repuestos/crear' . ($inspeccionId > 0 ? '/' . $inspeccionId : ''), 302);
            return;
        }

        $datos['aprendiz_id'] = $aprendizId;
        $total = 0.0;
        foreach ($items as $it) {
            $precio = (float) ($it['precio_unitario'] ?? 0);
            $cant = (float) preg_replace('/[^\d.,]/', '', (string) ($it['cantidad_tiempo'] ?? '1'));
            $total += $precio * ($cant > 0 ? $cant : 1);
        }
        $datos['total'] = $total;

        $model = new OrdenRepuestosModel();
        $ordenId = $model->crear($datos, $items);

        unset($_SESSION['orden_repuestos_errores'], $_SESSION['orden_repuestos_datos']);
        $this->redirect('/orden-repuestos?guardado=1', 302);
    }

    /**
     * Ver detalle de una orden.
     */
    public function ver(string $id): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $aprendizId = (int) ($usuario['id'] ?? 0);
        $ordenId = (int) $id;
        if ($ordenId < 1 || $aprendizId < 1) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $model = new OrdenRepuestosModel();
        if (!$model->puedeAccederAprendiz($ordenId, $aprendizId)) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }
        $orden = $model->obtenerPorId($ordenId);
        if ($orden === null) {
            $this->redirect('/orden-repuestos', 302);
            return;
        }

        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';
        $this->view('OrdenRepuestos.ver', [
            'titulo' => $nombreSistema . ' - Orden #' . $ordenId,
            'orden'  => $orden,
        ]);
    }

    /**
     * @param array<string, mixed> $cd
     * @return array<string, mixed>
     */
    private function mapearChecklistAOrden(array $cd): array
    {
        return [
            'cliente_nombre'    => $cd['cliente_nombre'] ?? $cd['asesor'] ?? '',
            'cliente_documento' => $cd['cliente_documento'] ?? $cd['tipo_comercial_codigo'] ?? '',
            'cliente_telefono'  => $cd['cliente_telefono'] ?? $cd['ldc'] ?? '',
            'cliente_email'     => $cd['cliente_email'] ?? $cd['vhn'] ?? '',
            'placa'             => $cd['placa'] ?? $cd['matricula'] ?? '',
            'modelo'            => $cd['modelo'] ?? $cd['tipo_comercial_modelo'] ?? '',
            'vin'               => $cd['bastidor'] ?? '',
            'ano'               => $cd['ano_modelo'] ?? null,
            'fecha_entrada'     => $cd['fecha_ingreso'] ?? $cd['fecha_servicio'] ?? date('Y-m-d'),
            'hora_entrada'      => $cd['hora_ingreso'] ?? $cd['djka'] ?? date('H:i'),
            'km_mto'            => $cd['kilometraje'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function extraerDatos(array $post): array
    {
        return [
            'inspeccion_id'       => (int) ($post['inspeccion_id'] ?? 0) ?: null,
            'cliente_nombre'      => trim((string) ($post['cliente_nombre'] ?? '')),
            'cliente_documento'   => trim((string) ($post['cliente_documento'] ?? '')),
            'cliente_direccion'   => trim((string) ($post['cliente_direccion'] ?? '')),
            'cliente_ciudad'      => trim((string) ($post['cliente_ciudad'] ?? '')),
            'cliente_telefono'    => trim((string) ($post['cliente_telefono'] ?? '')),
            'cliente_celular'     => trim((string) ($post['cliente_celular'] ?? '')),
            'cliente_email'       => trim((string) ($post['cliente_email'] ?? '')),
            'vin'                 => trim((string) ($post['vin'] ?? '')),
            'no_motor'            => trim((string) ($post['no_motor'] ?? '')),
            'placa'               => trim((string) ($post['placa'] ?? '')),
            'modelo'              => trim((string) ($post['modelo'] ?? '')),
            'color'               => trim((string) ($post['color'] ?? '')),
            'ano'                 => (int) ($post['ano'] ?? 0) ?: null,
            'fecha_entrada'       => trim((string) ($post['fecha_entrada'] ?? date('Y-m-d'))),
            'hora_entrada'        => trim((string) ($post['hora_entrada'] ?? '')),
            'fecha_prometida'     => trim((string) ($post['fecha_prometida'] ?? '')),
            'hora_prometida'      => trim((string) ($post['hora_prometida'] ?? '')),
            'km_mto'              => (int) ($post['km_mto'] ?? 0) ?: null,
            'rep_gral'            => trim((string) ($post['rep_gral'] ?? '')),
            'firma_recepcionista' => trim((string) ($post['firma_recepcionista'] ?? '')),
            'firma_cliente'       => trim((string) ($post['firma_cliente'] ?? '')),
            'cc_recepcionista'    => trim((string) ($post['cc_recepcionista'] ?? '')),
            'cc_cliente'          => trim((string) ($post['cc_cliente'] ?? '')),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extraerItems(array $post): array
    {
        $items = [];
        $refs = $post['item_referencia'] ?? [];
        $descs = $post['item_descripcion'] ?? [];
        $cants = $post['item_cantidad'] ?? [];
        $precios = $post['item_precio'] ?? [];
        if (!is_array($refs)) {
            $refs = [];
        }
        if (!is_array($descs)) {
            $descs = [];
        }
        if (!is_array($cants)) {
            $cants = [];
        }
        if (!is_array($precios)) {
            $precios = [];
        }
        $max = max(count($refs), count($descs), count($cants), count($precios));
        for ($i = 0; $i < $max; $i++) {
            $desc = trim((string) ($descs[$i] ?? ''));
            if ($desc === '') {
                continue;
            }
            $items[] = [
                'referencia'      => trim((string) ($refs[$i] ?? '')),
                'descripcion'     => $desc,
                'cantidad_tiempo' => trim((string) ($cants[$i] ?? '1')),
                'precio_unitario' => (float) ($precios[$i] ?? 0),
            ];
        }
        return $items;
    }

    /**
     * @param array<string, mixed> $datos
     * @param array<int, array<string, mixed>> $items
     * @return array<string>
     */
    private function validar(array $datos, array $items): array
    {
        $errores = [];
        if (trim($datos['cliente_nombre'] ?? '') === '') {
            $errores[] = 'Nombre del cliente es obligatorio.';
        }
        if (trim($datos['cliente_documento'] ?? '') === '') {
            $errores[] = 'NIT/C.C. es obligatorio.';
        }
        if (trim($datos['placa'] ?? '') === '') {
            $errores[] = 'Placa es obligatoria.';
        }
        if (trim($datos['fecha_entrada'] ?? '') === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha_entrada'])) {
            $errores[] = 'Fecha de entrada no válida.';
        }
        if ($items === []) {
            $errores[] = 'Debe agregar al menos un ítem con descripción.';
        }
        return $errores;
    }
}
