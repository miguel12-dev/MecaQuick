<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ChecklistDatosModel;
use App\Models\InspeccionModel;
use App\Models\PuntosCatalogoModel;
use App\Models\ResultadosPuntosModel;
use App\Services\AuthService;
use Core\BaseController;
use Throwable;

final class ChecklistController extends BaseController
{
    /**
     * Panel de revisión de checklists (solo instructor o admin).
     */
    public function panel(): void
    {
        AuthService::requireInstructor();
        $this->view('Checklist.panel', [
            'titulo' => 'MecaQuick - Panel de revisión de checklists',
        ]);
    }

    /**
     * JSON: fechas con al menos una revisión.
     */
    public function fechasDisponibles(): void
    {
        AuthService::requireInstructor();
        $inspeccionModel = new InspeccionModel();
        $fechas = $inspeccionModel->fechasConRevisiones();
        $this->json(['fechas' => $fechas]);
    }

    /**
     * JSON: revisiones del día (para filtro por fecha).
     */
    public function revisiones(): void
    {
        AuthService::requireInstructor();
        $fecha = trim((string) ($_GET['fecha'] ?? ''));
        if ($fecha === '') {
            $this->json(['revisiones' => []]);
            return;
        }
        $inspeccionModel = new InspeccionModel();
        $revisiones = $inspeccionModel->listarRevisionesPorFecha($fecha);
        $lista = [];
        foreach ($revisiones as $r) {
            $lista[] = [
                'id' => (int) $r['id'],
                'placa' => $r['placa'] ?? '—',
                'encargado' => $r['encargado'] ?? 'Sin asignar',
                'hora_inicio' => isset($r['inicio_at']) ? date('H:i', strtotime($r['inicio_at'])) : '—',
                'porcentaje_avance' => (int) ($r['porcentaje_avance'] ?? 0),
                'estado' => $r['estado'] ?? 'en_proceso',
            ];
        }
        $this->json(['revisiones' => $lista]);
    }

    /**
     * Detalle de una revisión (HTML o JSON según Accept / ajax=1 para polling).
     *
     * @param string $id ID de la inspección (primer segmento tras detalle)
     */
    public function detalle(string $id): void
    {
        AuthService::requireInstructor();
        $inspeccionId = (int) $id;
        if ($inspeccionId < 1) {
            if ($this->esPeticionJson()) {
                $this->json(['error' => 'ID inválido'], 400);
            } else {
                header('Location: /checklist/panel', true, 302);
                exit;
            }
        }

        $inspeccionModel = new InspeccionModel();
        $detalle = $inspeccionModel->obtenerDetalleParaInstructor($inspeccionId);
        if ($detalle === null) {
            if ($this->esPeticionJson()) {
                $this->json(['error' => 'Revisión no encontrada'], 404);
            } else {
                header('Location: /checklist/panel', true, 302);
                exit;
            }
        }

        if ($this->esPeticionJson()) {
            $payload = [
                'id' => (int) $detalle['id'],
                'placa' => $detalle['placa'] ?? '—',
                'encargado' => $detalle['encargado'] ?? 'Sin asignar',
                'hora_inicio' => isset($detalle['inicio_at']) ? date('H:i', strtotime($detalle['inicio_at'])) : '—',
                'porcentaje_avance' => (int) ($detalle['porcentaje_avance'] ?? 0),
                'estado' => $detalle['estado'] ?? 'en_proceso',
                'resultados' => array_map(static function (array $r): array {
                    return [
                        'numero_punto' => (int) ($r['numero_punto'] ?? 0),
                        'descripcion' => $r['punto_descripcion'] ?? '',
                        'estado' => $r['estado'] ?? '',
                        'valor_medido' => $r['valor_medido'] ?? null,
                        'observacion' => $r['observacion'] ?? null,
                        'evidencias' => $r['evidencias'] ?? [],
                    ];
                }, $detalle['resultados'] ?? []),
            ];
            $this->json($payload);
            return;
        }

        $this->view('Checklist.detalle', [
            'titulo' => 'MecaQuick - Detalle de revisión',
            'detalle' => $detalle,
        ]);
    }

    private function esPeticionJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'application/json') !== false) {
            return true;
        }
        return isset($_GET['ajax']) && (string) $_GET['ajax'] === '1';
    }

    /**
     * Formulario del checklist técnico. Requiere mecánico o mecánico líder.
     */
    public function index(): void
    {
        AuthService::requireMecanicoOrLider();
        try {
            $puntosModel = new PuntosCatalogoModel();
            $puntos = $puntosModel->listarActivos();
        } catch (Throwable $e) {
            $this->view('Checklist.index', [
                'titulo' => 'MecaQuick - Checklist técnico',
                'puntos' => [],
                'totalPuntos' => 0,
                'tokenInicial' => '',
                'errorDb' => 'Configure la base de datos: cree el archivo .env con DB_HOST, DB_NAME, DB_USER, DB_PASS y ejecute database/database.sql.',
            ]);
            return;
        }

        $tokenInicial = trim((string) ($_GET['token'] ?? ''));
        $usuario = AuthService::getLoggedUser();
        $redirectAprendizAlFinalizar = $usuario !== null && ($usuario['rol'] ?? '') === 'aprendiz';

        $datosPrecargados = [];
        $skipPasoCabecera = false;
        if ($tokenInicial !== '') {
            $inspeccionModel = new InspeccionModel();
            $inspeccion = $inspeccionModel->obtenerPorToken($tokenInicial);
            if ($inspeccion !== null) {
                $checklistDatosModel = new ChecklistDatosModel();
                $datosPrecargados = $checklistDatosModel->obtenerPorInspeccionId((int) $inspeccion['id']) ?? [];
                $skipPasoCabecera = $datosPrecargados !== [];
            }
        }

        $this->view('Checklist.index', [
            'titulo' => 'MecaQuick - Checklist técnico de vehículos',
            'puntos' => $puntos,
            'totalPuntos' => count($puntos),
            'tokenInicial' => $tokenInicial,
            'redirectAprendizAlFinalizar' => $redirectAprendizAlFinalizar,
            'datosPrecargados' => $datosPrecargados,
            'skipPasoCabecera' => $skipPasoCabecera,
        ]);
    }

    /**
     * Guarda el avance por paso cuando se pulsa "Siguiente".
     */
    public function guardarPaso(): void
    {
        AuthService::requireMecanicoOrLider();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(['ok' => false, 'message' => 'Método no permitido.'], 405);
        }

        $puntosModel = new PuntosCatalogoModel();
        $puntos = $puntosModel->listarActivos();
        $totalPuntos = count($puntos);
        $idsPuntos = array_column($puntos, 'id');

        $respuestasPermitidas = ['bueno', 'regular', 'malo', 'no_aplica'];

        $token = trim((string) ($_POST['token'] ?? ''));
        if ($token === '') {
            $token = bin2hex(random_bytes(32));
        }

        $finalizado = (int) ($_POST['finalizado'] ?? 0) === 1;
        $datos = $this->extraerDatosCabecera($_POST);
        $erroresCabecera = $this->validarCabecera($datos, $finalizado);
        if ($erroresCabecera !== []) {
            $this->json(['ok' => false, 'message' => implode(' ', $erroresCabecera)], 422);
        }

        $respuestasRequest = $_POST['responses'] ?? [];
        if (!is_array($respuestasRequest)) {
            $respuestasRequest = [];
        }

        $resultados = [];
        foreach ($respuestasRequest as $puntoIdStr => $valor) {
            $puntoId = (int) $puntoIdStr;
            if (!in_array($puntoId, $idsPuntos, true)) {
                continue;
            }
            $valorLimpio = strtolower(trim((string) $valor));
            if ($valorLimpio === '') {
                continue;
            }
            if (!in_array($valorLimpio, $respuestasPermitidas, true)) {
                $this->json(['ok' => false, 'message' => 'Valor de respuesta inválido para punto ' . $puntoIdStr . '.'], 422);
            }
            $resultados[$puntoId] = ['estado' => $valorLimpio];

            $observacion = $_POST['observaciones_punto'][$puntoIdStr] ?? null;
            if ($observacion !== null && trim((string) $observacion) !== '') {
                $resultados[$puntoId]['observacion'] = trim((string) $observacion);
            }
        }

        $preguntasRespondidas = count($resultados);

        if ($finalizado && $preguntasRespondidas !== $totalPuntos) {
            $this->json(
                ['ok' => false, 'message' => 'Debe completar todas las preguntas para finalizar.'],
                422
            );
        }

        $porcentajeAvance = $totalPuntos > 0
            ? (int) round(($preguntasRespondidas / $totalPuntos) * 100)
            : 0;

        try {
            $inspeccionModel = new InspeccionModel();
            $inspeccion = $inspeccionModel->obtenerPorToken($token);

            if ($inspeccion === null) {
                $inspeccionModel->crearStandalone($token);
                $inspeccion = $inspeccionModel->obtenerPorToken($token);
            }
            if ($inspeccion === null) {
                $this->json(['ok' => false, 'message' => 'No se pudo crear la inspección.'], 500);
            }

            $inspeccionId = (int) $inspeccion['id'];

            $checklistDatosModel = new ChecklistDatosModel();
            $checklistDatosModel->guardarOActualizar($inspeccionId, $datos);

            $resultadosModel = new ResultadosPuntosModel();
            $resultadosModel->guardarBatch($inspeccionId, $resultados);

            $inspeccionModel->actualizarAvance($inspeccionId, $porcentajeAvance, $finalizado);
        } catch (Throwable $e) {
            $this->json(['ok' => false, 'message' => 'No fue posible guardar el avance.'], 500);
        }

        $payload = [
            'ok' => true,
            'token' => $token,
            'progress' => [
                'answered' => $preguntasRespondidas,
                'total' => $totalPuntos,
                'percentage' => $porcentajeAvance,
            ],
        ];
        if ($finalizado) {
            $payload['inspeccion_id'] = $inspeccionId;
        }
        $this->json($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function extraerDatosCabecera(array $post): array
    {
        return [
            'nombre_cliente' => trim((string) ($post['nombre_cliente'] ?? '')),
            'cedula_nit' => trim((string) ($post['cedula_nit'] ?? '')),
            'telefono' => preg_replace('/\D/', '', (string) ($post['telefono'] ?? '')),
            'correo' => trim((string) ($post['correo'] ?? '')),
            'modelo_vehiculo' => trim((string) ($post['modelo_vehiculo'] ?? '')),
            'placa' => trim((string) ($post['placa'] ?? '')),
            'kilometraje' => (int) ($post['kilometraje'] ?? 0),
            'fecha_ingreso' => trim((string) ($post['fecha_ingreso'] ?? '')),
            'hora_ingreso' => trim((string) ($post['hora_ingreso'] ?? '')),
            'observaciones_generales' => trim((string) ($post['observaciones_generales'] ?? '')),
            'firma_tecnico' => trim((string) ($post['firma_tecnico'] ?? '')),
            'nombre_tecnico' => trim((string) ($post['nombre_tecnico'] ?? '')),
            'firma_cliente' => trim((string) ($post['firma_cliente'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $datos
     * @return array<string>
     */
    private function validarCabecera(array $datos, bool $finalizado): array
    {
        $errores = [];

        $camposRequeridos = [
            'nombre_cliente' => 'Nombre del cliente',
            'cedula_nit' => 'Cédula / NIT',
            'telefono' => 'Teléfono',
            'correo' => 'Correo',
            'modelo_vehiculo' => 'Modelo del vehículo',
            'placa' => 'Placa',
            'kilometraje' => 'Kilometraje',
            'fecha_ingreso' => 'Fecha de ingreso',
            'hora_ingreso' => 'Hora',
        ];

        if ($finalizado) {
            $camposRequeridos['nombre_tecnico'] = 'Nombre del técnico';
        }

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
            if ($campo === 'telefono') {
                if (!preg_match('/^3\d{9}$/', $v)) {
                    $errores[] = $etiqueta . ' debe tener 10 dígitos e iniciar por 3.';
                }
            }
            if ($campo === 'placa') {
                if (!preg_match('/^[A-Za-z]{3}\d{3}$/i', $v)) {
                    $errores[] = $etiqueta . ' debe ser 3 letras seguidas de 3 números (ej. ABC123).';
                }
            }
            if ($campo === 'correo') {
                if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = $etiqueta . ' no es válido.';
                }
            }
            if ($campo === 'cedula_nit') {
                if (!preg_match('/^\d+$/', $v)) {
                    $errores[] = $etiqueta . ' solo debe contener números.';
                }
            }
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($datos['fecha_ingreso'] ?? ''))) {
            $errores[] = 'Fecha de ingreso no válida.';
        }

        return $errores;
    }
}
