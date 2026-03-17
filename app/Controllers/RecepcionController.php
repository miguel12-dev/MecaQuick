<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CitaModel;
use App\Models\ChecklistDatosModel;
use App\Models\ConfiguracionModel;
use App\Models\InspeccionAyudantesModel;
use App\Models\InspeccionModel;
use App\Models\OrdenRepuestosModel;
use App\Models\UsuarioSistemaModel;
use App\Services\AuthService;
use Core\BaseController;
use Throwable;

/**
 * Módulo de recepción. Acceso exclusivo para rol aprendiz.
 */
final class RecepcionController extends BaseController
{
    public function index(): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $this->view('Recepcion.index', [
            'titulo'        => $nombreSistema . ' - Módulo de recepción',
            'nombreSistema' => $nombreSistema,
            'usuario'       => $usuario,
        ]);
    }

    public function aprendizaje(): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $this->view('Recepcion.aprendizaje', [
            'titulo'        => $nombreSistema . ' - Aprendizaje',
            'nombreSistema' => $nombreSistema,
            'usuario'       => $usuario,
        ]);
    }

    public function misRevisiones(): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $aprendizId = (int) ($usuario['id'] ?? 0);
        if ($aprendizId < 1) {
            $this->redirect('/recepcion', 302);
            return;
        }

        $inspeccionModel = new InspeccionModel();
        $revisiones = $inspeccionModel->listarPorAprendiz($aprendizId);

        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';
        $this->view('Recepcion.mis_revisiones', [
            'titulo'     => $nombreSistema . ' - Mis revisiones',
            'usuario'    => $usuario,
            'revisiones' => $revisiones,
        ]);
    }

    /**
     * Panel de citas del día o formulario de recepción.
     * Sin parámetro: muestra citas de hoy. Con cita_id: formulario prellenado. Con "sin-cita": formulario vacío.
     */
    public function crear(?string $citaId = null): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        if ($citaId === null || $citaId === '') {
            $citaModel = new CitaModel();
            $citas = $citaModel->listarCitasHoy();
            $this->view('Recepcion.crear_citas', [
                'titulo' => $nombreSistema . ' - Citas del día',
                'usuario' => $usuario,
                'citas'   => $citas,
            ]);
            return;
        }

        if ($citaId === 'sin-cita') {
            $this->mostrarFormularioRecepcion($usuario, $nombreSistema, []);
            return;
        }

        $citaIdInt = (int) $citaId;
        if ($citaIdInt < 1) {
            $this->redirect('/recepcion/crear', 302);
            return;
        }

        $citaModel = new CitaModel();
        $cita = $citaModel->obtenerPorIdParaRecepcion($citaIdInt);
        if ($cita === null) {
            $this->redirect('/recepcion/crear', 302);
            return;
        }

        $datos = $this->mapearCitaADatosRecepcion($cita);
        $this->mostrarFormularioRecepcion($usuario, $nombreSistema, $datos, $citaIdInt);
    }

    /**
     * @param array<string, mixed> $datosPrellenados
     */
    private function mostrarFormularioRecepcion(
        array $usuario,
        string $nombreSistema,
        array $datosPrellenados,
        ?int $citaId = null
    ): void {
        $tutorId = ConfiguracionModel::get('tutor_recepcion_id');
        $nombreTutor = '';
        if (is_numeric($tutorId) && (int) $tutorId > 0) {
            $usuarioModel = new UsuarioSistemaModel();
            $tutor = $usuarioModel->findById((int) $tutorId);
            $nombreTutor = $tutor['nombre'] ?? '';
        }

        $datos = array_merge($_SESSION['mantenimiento_crear_datos'] ?? [], $datosPrellenados);

        $this->view('Recepcion.crear', [
            'titulo'       => $nombreSistema . ' - Crear recepción',
            'usuario'      => $usuario,
            'nombreTutor'  => $nombreTutor,
            'datos'        => $datos,
            'cita_id'      => $citaId,
        ]);
    }

    /**
     * @param array<string, mixed> $cita
     * @return array<string, mixed>
     */
    private function mapearCitaADatosRecepcion(array $cita): array
    {
        $nombre = trim(($cita['nombre'] ?? '') . ' ' . ($cita['apellido'] ?? ''));
        $modelo = trim(($cita['marca'] ?? '') . ' ' . ($cita['modelo'] ?? ''));
        $fechaVenta = '';
        if (!empty($cita['fecha'])) {
            $fechaVenta = (string) $cita['fecha'];
        }

        return [
            'cliente_nombre'        => $nombre,
            'cliente_documento'     => (string) ($cita['documento'] ?? ''),
            'cliente_telefono'      => (string) ($cita['telefono'] ?? ''),
            'cliente_email'         => (string) ($cita['email'] ?? ''),
            'matricula'             => (string) ($cita['placa'] ?? ''),
            'tipo_comercial_modelo' => $modelo !== '' ? $modelo : (string) ($cita['modelo'] ?? ''),
            'fecha_ingreso'         => $fechaVenta !== '' ? $fechaVenta : date('Y-m-d'),
            'hora_ingreso'          => date('H:i'),
            'kilometraje'           => (int) ($cita['kilometraje'] ?? 0),
            'observaciones'         => (string) ($cita['observaciones_cliente'] ?? ''),
        ];
    }

    public function guardarNuevo(): void
    {
        AuthService::requireAprendiz();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/recepcion/crear', 302);
            return;
        }

        $usuario = AuthService::getLoggedUser();
        $aprendizId = (int) ($usuario['id'] ?? 0);
        if ($aprendizId < 1) {
            $this->redirect('/recepcion', 302);
            return;
        }

        $datos = $this->extraerDatosCabecera($_POST);
        $errores = $this->validarCabeceraInicial($datos);
        if ($errores !== []) {
            $_SESSION['mantenimiento_crear_errores'] = $errores;
            $_SESSION['mantenimiento_crear_datos'] = $datos;
            $citaId = trim((string) ($_POST['cita_id'] ?? ''));
            $suffix = ($citaId !== '' && $citaId !== 'sin-cita' && ctype_digit($citaId)) ? $citaId : 'sin-cita';
            $this->redirect('/recepcion/crear/' . $suffix, 302);
            return;
        }

        $tutorId = ConfiguracionModel::get('tutor_recepcion_id');
        $instructorId = null;
        if (is_numeric($tutorId) && (int) $tutorId > 0) {
            $instructorId = (int) $tutorId;
        }

        $token = bin2hex(random_bytes(32));
        try {
            $inspeccionModel = new InspeccionModel();
            $inspeccionId = $inspeccionModel->crearDesdeMantenimiento($token, $aprendizId, $instructorId);

            $checklistDatosModel = new ChecklistDatosModel();
            $checklistDatosModel->guardarOActualizar($inspeccionId, $datos);
        } catch (Throwable $e) {
            $_SESSION['mantenimiento_crear_errores'] = ['No se pudo crear la recepción. Intente de nuevo.'];
            $_SESSION['mantenimiento_crear_datos'] = $datos;
            $citaId = trim((string) ($_POST['cita_id'] ?? ''));
            $suffix = ($citaId !== '' && $citaId !== 'sin-cita' && ctype_digit($citaId)) ? $citaId : 'sin-cita';
            $this->redirect('/recepcion/crear/' . $suffix, 302);
            return;
        }

        unset($_SESSION['mantenimiento_crear_errores'], $_SESSION['mantenimiento_crear_datos']);
        $this->redirect('/checklist?token=' . urlencode($token), 302);
    }

    /**
     * Detalle de una revisión (solo lectura). Ruta: /recepcion/revision/{id}
     */
    public function revision(string $id): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $aprendizId = (int) ($usuario['id'] ?? 0);
        $inspeccionId = (int) $id;
        if ($aprendizId < 1 || $inspeccionId < 1) {
            $this->redirect('/recepcion/mis-revisiones', 302);
            return;
        }

        $inspeccionModel = new InspeccionModel();
        $detalle = $inspeccionModel->obtenerDetalleParaAprendiz($inspeccionId, $aprendizId);
        if ($detalle === null) {
            $this->redirect('/recepcion/mis-revisiones', 302);
            return;
        }

        $ayudantesModel = new InspeccionAyudantesModel();
        $ayudantes = $ayudantesModel->listarPorInspeccion($inspeccionId);

        $inspeccionBasica = $inspeccionModel->obtenerBasica($inspeccionId);
        $esResponsable = $inspeccionBasica !== null && (int) $inspeccionBasica['aprendiz_id'] === $aprendizId;
        $mostrarFormAyudantes = $esResponsable && ($inspeccionBasica['estado'] ?? '') === 'finalizada';

        $listaAprendices = [];
        if ($mostrarFormAyudantes) {
            $usuarioModel = new UsuarioSistemaModel();
            $todos = $usuarioModel->listarPorRol('aprendiz');
            foreach ($todos as $a) {
                if ((int) $a['id'] !== $aprendizId) {
                    $listaAprendices[] = $a;
                }
            }
        }

        $ordenRepuestosModel = new OrdenRepuestosModel();
        $ordenRepuestos = $ordenRepuestosModel->obtenerPorInspeccionId($inspeccionId);
        $tieneOrden = $ordenRepuestos !== null;
        $ordenRepuestosId = $tieneOrden ? (int) $ordenRepuestos['id'] : 0;
        $mostrarLinkOrden = ($inspeccionBasica['estado'] ?? '') === 'finalizada';

        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';
        $this->view('Recepcion.revision', [
            'titulo'               => $nombreSistema . ' - Detalle de revisión',
            'usuario'              => $usuario,
            'detalle'              => $detalle,
            'ayudantes'            => $ayudantes,
            'inspeccion_id'        => $inspeccionId,
            'mostrarFormAyudantes' => $mostrarFormAyudantes,
            'listaAprendices'      => $listaAprendices,
            'tieneOrden'           => $tieneOrden,
            'ordenRepuestosId'    => $ordenRepuestosId,
            'mostrarLinkOrden'     => $mostrarLinkOrden,
        ]);
    }

    public function agregarAyudantes(): void
    {
        AuthService::requireAprendiz();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/recepcion', 302);
            return;
        }

        $usuario = AuthService::getLoggedUser();
        $aprendizId = (int) ($usuario['id'] ?? 0);
        $inspeccionId = (int) ($_POST['inspeccion_id'] ?? 0);
        $aprendizIds = $_POST['ayudantes'] ?? [];
        if (!is_array($aprendizIds)) {
            $aprendizIds = [];
        }

        if ($aprendizId < 1 || $inspeccionId < 1) {
            $this->redirect('/recepcion/mis-revisiones', 302);
            return;
        }

        $inspeccionModel = new InspeccionModel();
        $inspeccion = $inspeccionModel->obtenerBasica($inspeccionId);
        if ($inspeccion === null || $inspeccion['aprendiz_id'] !== $aprendizId || $inspeccion['estado'] !== 'finalizada') {
            $this->redirect('/recepcion/mis-revisiones', 302);
            return;
        }

        $ayudantesModel = new InspeccionAyudantesModel();
        $ayudantesModel->agregarVarios($inspeccionId, $aprendizId, $aprendizIds);

        $this->redirect('/recepcion/revision/' . $inspeccionId, 302);
    }

    /**
     * Extrae datos del formato checklist técnico (sección 1) y los mapea al esquema interno.
     *
     * @return array<string, mixed>
     */
    private function extraerDatosCabecera(array $post): array
    {
        $clienteNombre = trim((string) ($post['cliente_nombre'] ?? $post['asesor'] ?? ''));
        if ($clienteNombre === '') {
            $clienteNombre = 'Cliente';
        }

        $fechaIngreso = trim((string) ($post['fecha_ingreso'] ?? $post['fecha_servicio'] ?? ''));
        $horaIngreso = trim((string) ($post['hora_ingreso'] ?? $post['djka'] ?? date('H:i')));
        if (strlen($horaIngreso) > 20) {
            $horaIngreso = substr($horaIngreso, 0, 20);
        }

        $correo = trim((string) ($post['cliente_email'] ?? $post['vhn'] ?? ''));
        if (strlen($correo) > 20) {
            $correo = substr($correo, 0, 20);
        }

        return [
            'numero_orden' => trim((string) ($post['numero_orden'] ?? 'N-' . date('YmdHis'))),
            'tipo_comercial_codigo' => trim((string) ($post['cliente_documento'] ?? $post['tipo_comercial_codigo'] ?? '')),
            'matricula' => trim((string) ($post['matricula'] ?? '')),
            'matriculacion' => $this->date($post['matriculacion'] ?? $fechaIngreso),
            'bastidor' => trim((string) ($post['bastidor'] ?? '-')),
            'ldm' => trim((string) ($post['ldm'] ?? '')),
            'djka' => $horaIngreso,
            'kilometraje' => (int) ($post['kilometraje'] ?? 0),
            'asesor' => $clienteNombre,
            'tipo_comercial_modelo' => trim((string) ($post['tipo_comercial_modelo'] ?? '')),
            'ldc' => trim((string) ($post['cliente_telefono'] ?? $post['ldc'] ?? '')),
            'vhn' => $correo,
            'fecha_servicio' => $fechaIngreso !== '' ? $fechaIngreso : date('Y-m-d'),
            'tipo_inspeccion' => trim((string) ($post['tipo_inspeccion'] ?? 'Inspección técnica')),
            'km_salida' => trim((string) ($post['km_salida'] ?? '')),
            'km_llegada' => trim((string) ($post['km_llegada'] ?? '')),
            'observaciones' => trim((string) ($post['observaciones'] ?? '')),
            'nota_mantenimiento' => trim((string) ($post['nota_mantenimiento'] ?? '')),
            'fecha_firma_responsable' => trim((string) ($post['fecha_firma_responsable'] ?? '')),
            'fecha_firma_control' => trim((string) ($post['fecha_firma_control'] ?? '')),
            'carroceria_json' => null,
            'nivel_combustible' => null,
        ];
    }

    private function date(?string $v): ?string
    {
        if ($v === null || $v === '' || !preg_match('/^\d{4}-\d{2}-\d{2}/', trim((string) $v))) {
            return null;
        }
        return substr(trim($v), 0, 10);
    }

    private function int(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        $n = (int) $v;
        return $n >= 0 ? $n : null;
    }

    /**
     * @param array<string, mixed> $datos
     * @return array<string>
     */
    private function validarCabeceraInicial(array $datos): array
    {
        $errores = [];
        $camposRequeridos = [
            'asesor' => 'Nombre del cliente',
            'tipo_comercial_codigo' => 'Cédula / NIT',
            'ldc' => 'Teléfono',
            'vhn' => 'Correo',
            'matricula' => 'Placa',
            'tipo_comercial_modelo' => 'Modelo del vehículo',
            'kilometraje' => 'Kilometraje',
            'fecha_servicio' => 'Fecha de ingreso',
            'djka' => 'Hora',
        ];

        foreach ($camposRequeridos as $campo => $etiqueta) {
            $v = trim((string) ($datos[$campo] ?? ''));
            if ($v === '') {
                $errores[] = $etiqueta . ' es obligatorio.';
                continue;
            }
            if ($campo === 'kilometraje') {
                $n = (int) $v;
                if ($n < 0) {
                    $errores[] = $etiqueta . ' no puede ser negativo.';
                }
            }
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($datos['fecha_servicio'] ?? ''))) {
            $errores[] = 'Fecha de ingreso no válida.';
        }

        return $errores;
    }
}
