<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ChecklistDatosModel;
use App\Models\ConfiguracionModel;
use App\Models\InspeccionAyudantesModel;
use App\Models\InspeccionModel;
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

    public function crear(): void
    {
        AuthService::requireAprendiz();
        $usuario = AuthService::getLoggedUser();
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $tutorId = ConfiguracionModel::get('tutor_mantenimiento_id');
        $nombreTutor = '';
        if (is_numeric($tutorId) && (int) $tutorId > 0) {
            $usuarioModel = new UsuarioSistemaModel();
            $tutor = $usuarioModel->findById((int) $tutorId);
            $nombreTutor = $tutor['nombre'] ?? '';
        }

        $this->view('Recepcion.crear', [
            'titulo'       => $nombreSistema . ' - Crear recepción',
            'usuario'      => $usuario,
            'nombreTutor'  => $nombreTutor,
        ]);
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
            $this->redirect('/recepcion/crear', 302);
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
            $this->redirect('/recepcion/crear', 302);
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

        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';
        $this->view('Mantenimiento.revision', [
            'titulo'               => $nombreSistema . ' - Detalle de revisión',
            'usuario'              => $usuario,
            'detalle'              => $detalle,
            'ayudantes'            => $ayudantes,
            'inspeccion_id'        => $inspeccionId,
            'mostrarFormAyudantes' => $mostrarFormAyudantes,
            'listaAprendices'      => $listaAprendices,
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
     * @return array<string, mixed>
     */
    private function extraerDatosCabecera(array $post): array
    {
        return [
            'numero_orden' => trim((string) ($post['numero_orden'] ?? '')),
            'tipo_comercial_codigo' => trim((string) ($post['tipo_comercial_codigo'] ?? '')),
            'matricula' => trim((string) ($post['matricula'] ?? '')),
            'matriculacion' => trim((string) ($post['matriculacion'] ?? '')),
            'bastidor' => trim((string) ($post['bastidor'] ?? '')),
            'ldm' => trim((string) ($post['ldm'] ?? '')),
            'djka' => trim((string) ($post['djka'] ?? '')),
            'kilometraje' => (int) ($post['kilometraje'] ?? 0),
            'asesor' => trim((string) ($post['asesor'] ?? '')),
            'tipo_comercial_modelo' => trim((string) ($post['tipo_comercial_modelo'] ?? '')),
            'ldc' => trim((string) ($post['ldc'] ?? '')),
            'vhn' => trim((string) ($post['vhn'] ?? '')),
            'ano_modelo' => trim((string) ($post['ano_modelo'] ?? '')),
            'fecha_servicio' => trim((string) ($post['fecha_servicio'] ?? '')),
            'tipo_inspeccion' => trim((string) ($post['tipo_inspeccion'] ?? '')),
            'km_salida' => trim((string) ($post['km_salida'] ?? '')),
            'km_llegada' => trim((string) ($post['km_llegada'] ?? '')),
            'observaciones' => trim((string) ($post['observaciones'] ?? '')),
            'nota_mantenimiento' => trim((string) ($post['nota_mantenimiento'] ?? '')),
            'fecha_firma_responsable' => trim((string) ($post['fecha_firma_responsable'] ?? '')),
            'fecha_firma_control' => trim((string) ($post['fecha_firma_control'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $datos
     * @return array<string>
     */
    private function validarCabeceraInicial(array $datos): array
    {
        $errores = [];
        $camposRequeridos = [
            'numero_orden' => 'Número de orden',
            'tipo_comercial_codigo' => 'Tipo comercial (código)',
            'matricula' => 'Matrícula',
            'matriculacion' => 'Matriculación',
            'bastidor' => 'Número de bastidor',
            'kilometraje' => 'Kilometraje',
            'fecha_servicio' => 'Fecha de servicio',
            'asesor' => 'Asesor del servicio',
            'tipo_comercial_modelo' => 'Tipo comercial (modelo)',
            'ano_modelo' => 'Año de modelos',
            'tipo_inspeccion' => 'Tipo de inspección',
        ];

        foreach ($camposRequeridos as $campo => $etiqueta) {
            $v = trim((string) ($datos[$campo] ?? ''));
            if ($v === '') {
                $errores[] = $etiqueta . ' es obligatorio.';
                continue;
            }
            if (in_array($campo, ['kilometraje'], true)) {
                $n = (int) $v;
                if ($n < 0) {
                    $errores[] = $etiqueta . ' no puede ser negativo.';
                }
            }
            if ($campo === 'ano_modelo') {
                $n = (int) $v;
                if ($n < 1950 || $n > 2030) {
                    $errores[] = $etiqueta . ' debe ser entre 1950 y 2030.';
                }
            }
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($datos['fecha_servicio'] ?? ''))) {
            $errores[] = 'Fecha de servicio no válida.';
        }

        return $errores;
    }
}
