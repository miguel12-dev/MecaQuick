<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CitaModel;
use App\Models\ClienteModel;
use App\Models\VehiculoModel;
use App\Models\InspeccionModel;
use App\Models\RecepcionModel;
use App\Models\ChecklistDatosModel;
use Core\BaseController;
use Throwable;

/**
 * Controlador del módulo de recepción del vehículo.
 * Flujo: Cita (hoy) → Recepción → Checklist.
 */
final class RecepcionController extends BaseController
{
    /**
     * Lista citas del día. Si no hay, muestra mensaje.
     */
    public function index(): void
    {
        try {
            $citaModel = new CitaModel();
            $citas = $citaModel->listarCitasHoy();
        } catch (Throwable $e) {
            $this->view('Recepcion.select', [
                'titulo' => 'Recepción - MecaQuick',
                'citas' => [],
                'errorDb' => 'Error al conectar con la base de datos.',
            ]);
            return;
        }

        $this->view('Recepcion.select', [
            'titulo' => 'Recepción - MecaQuick',
            'citas' => $citas,
            'errorDb' => null,
        ]);
    }

    /**
     * Formulario de recepción para una cita.
     */
    public function form(string $citaId): void
    {
        $id = (int) $citaId;
        if ($id <= 0) {
            $this->redirect('/recepcion');
        }

        try {
            $citaModel = new CitaModel();
            $cita = $citaModel->obtenerCompletaPorId($id);
        } catch (Throwable $e) {
            $this->redirect('/recepcion');
        }

        if ($cita === null) {
            $this->redirect('/recepcion');
        }

        $recepcionModel = new RecepcionModel();
        $recepcion = $recepcionModel->obtenerPorCitaId($id);

        $this->view('Recepcion.form', [
            'titulo' => 'Recepción del vehículo - MecaQuick',
            'cita' => $cita,
            'recepcion' => $recepcion,
            'accesoriosInternos' => $this->getAccesoriosInternos(),
            'accesoriosExternos' => $this->getAccesoriosExternos(),
        ]);
    }

    /**
     * Guarda recepción, crea inspección, checklist_datos y redirige al checklist.
     */
    public function guardar(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/recepcion');
        }

        $citaId = (int) ($_POST['cita_id'] ?? 0);
        if ($citaId <= 0) {
            $this->redirect('/recepcion');
        }

        try {
            $citaModel = new CitaModel();
            $cita = $citaModel->obtenerCompletaPorId($citaId);
        } catch (Throwable $e) {
            $this->redirect('/recepcion');
        }

        if ($cita === null) {
            $this->redirect('/recepcion');
        }

        $clienteId = (int) $cita['cliente_id'];
        $vehiculoId = (int) $cita['vehiculo_id'];

        // Actualizar cliente (dirección, ciudad)
        $clienteModel = new ClienteModel();
        $clienteModel->actualizarDireccionCiudad(
            $clienteId,
            $this->str($_POST['direccion'] ?? null),
            $this->str($_POST['ciudad'] ?? null)
        );

        // Actualizar vehículo (vin, numero_motor, kilometraje, fecha_venta)
        $vehiculoModel = new VehiculoModel();
        $km = $this->int($_POST['kilometraje_recepcion'] ?? null);
        $vehiculoModel->actualizarDatosRecepcion(
            $vehiculoId,
            $this->str($_POST['vin'] ?? null),
            $this->str($_POST['numero_motor'] ?? null),
            $km,
            $this->date($_POST['fecha_venta'] ?? null)
        );

        // Crear inspección desde cita
        $inspeccionModel = new InspeccionModel();
        $inspeccion = $inspeccionModel->crearDesdeCita($citaId);
        $inspeccionId = (int) $inspeccion['id'];
        $token = (string) $inspeccion['token'];

        // Guardar recepción
        $recepcionModel = new RecepcionModel();
        $recepcionModel->guardarOActualizar($citaId, $this->extraerDatosRecepcion($_POST), $inspeccionId);

        // Crear checklist_datos con datos precargados
        $checklistDatosModel = new ChecklistDatosModel();
        $nombreCompleto = trim($cita['nombre'] . ' ' . $cita['apellido']);
        $modeloVehiculo = trim(($cita['marca'] ?? '') . ' ' . ($cita['modelo'] ?? '') . ' ' . ($cita['anio'] ?? ''));
        $checklistDatosModel->guardarOActualizar($inspeccionId, [
            'nombre_cliente' => $nombreCompleto,
            'cedula_nit' => (string) ($cita['documento'] ?? ''),
            'telefono' => (string) ($cita['telefono'] ?? ''),
            'correo' => (string) ($cita['email'] ?? ''),
            'modelo_vehiculo' => $modeloVehiculo,
            'placa' => strtoupper((string) ($cita['placa'] ?? '')),
            'kilometraje' => $km ?? (int) ($cita['kilometraje'] ?? 0),
            'fecha_ingreso' => date('Y-m-d'),
            'hora_ingreso' => date('H:i'),
            'nombre_tecnico' => $this->str($_POST['inventariado_por'] ?? '') ?? 'Técnico',
        ]);
        $this->redirect('/checklist?token=' . rawurlencode($token));
    }

    /**
     * @return array<string, mixed>
     */
    private function extraerDatosRecepcion(array $post): array
    {
        $accesoriosInt = [];
        $accesoriosExt = [];
        foreach ($this->getAccesoriosInternos() as $key => $label) {
            $accesoriosInt[$key] = isset($post['accesorio_int_' . $key]) && $post['accesorio_int_' . $key] === 'si' ? 'si' : 'no';
        }
        foreach ($this->getAccesoriosExternos() as $key => $label) {
            $accesoriosExt[$key] = isset($post['accesorio_ext_' . $key]) && $post['accesorio_ext_' . $key] === 'si' ? 'si' : 'no';
        }

        return [
            'kilometraje_recepcion' => $this->int($post['kilometraje_recepcion'] ?? null),
            'fecha_servicio_anterior' => $this->date($post['fecha_servicio_anterior'] ?? null),
            'or_numero' => $this->str($post['or_numero'] ?? null),
            'tipo_servicio_anterior' => $this->str($post['tipo_servicio_anterior'] ?? null),
            'km_servicio_anterior' => $this->int($post['km_servicio_anterior'] ?? null),
            'vehiculo_conducido_por' => $this->str($post['vehiculo_conducido_por'] ?? 'dueno'),
            'presupuesto_repuestos' => (float) ($post['presupuesto_repuestos'] ?? 0),
            'presupuesto_mano_obra' => (float) ($post['presupuesto_mano_obra'] ?? 0),
            'presupuesto_total' => (float) ($post['presupuesto_total'] ?? 0),
            'metodo_pago' => $this->str($post['metodo_pago'] ?? 'efectivo'),
            'accesorios_internos' => $accesoriosInt,
            'accesorios_externos' => $accesoriosExt,
            'recibo_repuesto_cambiados' => isset($post['recibo_repuesto_cambiados']) ? 1 : 0,
            'observaciones' => $this->str($post['observaciones'] ?? null),
            'defectos_carroceria' => $this->str($post['defectos_carroceria'] ?? null),
            'inventariado_por' => $this->str($post['inventariado_por'] ?? null),
            'inventariado_cc' => $this->str($post['inventariado_cc'] ?? null),
            'firma_cliente' => $this->str($post['firma_cliente'] ?? null),
            'firma_cliente_cc' => $this->str($post['firma_cliente_cc'] ?? null),
            'autorizacion_adicional' => $this->float($post['autorizacion_adicional'] ?? null),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getAccesoriosInternos(): array
    {
        return [
            'gato' => 'Gato',
            'llave_pernos' => 'Llave de pernos',
            'triangulo' => 'Triángulo de seguridad',
            'botiquin' => 'Botiquín',
            'extintor' => 'Extintor',
            'manual' => 'Manual del propietario',
            'llave_repuesto' => 'Llave de repuesto',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getAccesoriosExternos(): array
    {
        return [
            'tapas_rin' => 'Tapas de rines',
            'tapa_gasolina' => 'Tapa de gasolina',
            'antena' => 'Antena',
            'espejos' => 'Espejos retrovisores',
        ];
    }

    private function str(?string $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        return trim($v);
    }

    private function date(?string $v): ?string
    {
        if ($v === null || $v === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', trim((string) $v))) {
            return null;
        }
        return trim((string) $v);
    }

    private function int(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        $n = (int) $v;
        return $n >= 0 ? $n : null;
    }

    private function float(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        return (float) $v;
    }
}
