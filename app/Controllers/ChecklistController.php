<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ChecklistDatosModel;
use App\Models\CitaModel;
use App\Models\FechaDisponibleModel;
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
        $fechaDisponibleModel = new FechaDisponibleModel();
        $fechasInspecciones = $inspeccionModel->fechasConRevisiones();
        $fechasConfiguradas = $fechaDisponibleModel->listarTodasLasFechas();
        $fechas = array_unique(array_merge($fechasInspecciones, $fechasConfiguradas));
        $hoy = date('Y-m-d');
        if (!in_array($hoy, $fechas, true)) {
            $fechas[] = $hoy;
        }
        rsort($fechas);
        $this->json(['fechas' => array_values($fechas)]);
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
     * Acceso principal al checklist.
     *
     * - Sin token: lista checklists asignados al aprendiz (hoy primero).
     * - Con token: abre el formulario del checklist para continuar.
     */
    public function index(): void
    {
        $tokenInicial = isset($_GET['token']) ? trim((string) $_GET['token']) : '';
        if ($tokenInicial === '') {
            AuthService::requireAprendiz();
            $usuario = AuthService::getLoggedUser();
            $aprendizId = (int) ($usuario['id'] ?? 0);
            if ($aprendizId < 1) {
                header('Location: /login', true, 302);
                exit;
            }

            $inspeccionModel = new InspeccionModel();
            $vehiculos = $inspeccionModel->listarPorAprendiz($aprendizId);

            $hoy = date('Y-m-d');
            usort($vehiculos, static function (array $a, array $b) use ($hoy): int {
                $aTs = isset($a['inicio_at']) ? strtotime((string) $a['inicio_at']) : 0;
                $bTs = isset($b['inicio_at']) ? strtotime((string) $b['inicio_at']) : 0;
                $aHoy = $aTs > 0 && date('Y-m-d', $aTs) === $hoy;
                $bHoy = $bTs > 0 && date('Y-m-d', $bTs) === $hoy;
                if ($aHoy !== $bHoy) {
                    return $aHoy ? -1 : 1;
                }
                return $bTs <=> $aTs;
            });

            $this->view('Checklist.lista_vehiculos', [
                'titulo' => 'MecaQuick - Checklist de vehículos',
                'vehiculos' => $vehiculos,
                'usuario' => $usuario,
            ]);
            return;
        }

        try {
            $puntosModel = new PuntosCatalogoModel();
            $puntos = $puntosModel->listarActivos();
        } catch (Throwable $e) {
            $this->view('Checklist.index', [
                'titulo' => 'MecaQuick - Checklist de mantenimiento',
                'puntos' => [],
                'totalPuntos' => 0,
                'errorDb' => 'Configure la base de datos: cree el archivo .env con DB_HOST, DB_NAME, DB_USER, DB_PASS y ejecute database/database.sql.',
            ]);
            return;
        }

        $usuario = AuthService::getLoggedUser();
        $redirectAprendizAlFinalizar = $usuario !== null && ($usuario['rol'] ?? '') === 'aprendiz';

        $cabeceraPrecargada = null;
        $skipPasoCabecera = false;
        $respuestasGuardadas = [];
        $startStepIndex = 0;
        if ($tokenInicial !== '') {
            $inspeccionModel = new InspeccionModel();
            $inspeccion = $inspeccionModel->obtenerPorToken($tokenInicial);
            if ($inspeccion === null) {
                header('Location: /checklist', true, 302);
                exit;
            }

            if ($usuario !== null && ($usuario['rol'] ?? '') === 'aprendiz') {
                $aprendizId = (int) ($usuario['id'] ?? 0);
                $inspeccionId = (int) ($inspeccion['id'] ?? 0);
                if ($aprendizId < 1 || $inspeccionId < 1 || !$inspeccionModel->puedeVerAprendiz($inspeccionId, $aprendizId)) {
                    header('Location: /checklist', true, 302);
                    exit;
                }
            }

            $checklistDatosModel = new ChecklistDatosModel();
            $cabeceraPrecargada = $checklistDatosModel->obtenerPorInspeccionId((int) $inspeccion['id']);
            $skipPasoCabecera = $cabeceraPrecargada !== null;

            $resultadosModel = new ResultadosPuntosModel();
            $respuestasGuardadas = $resultadosModel->obtenerPorInspeccion((int) $inspeccion['id']);

            // Reanudar en el primer punto pendiente (o en final si están todos).
            $startStepIndex = $skipPasoCabecera ? 1 : 0;
            $primerPendienteIdx = null;
            foreach (($puntos ?? []) as $idx => $punto) {
                $pid = (int) ($punto['id'] ?? 0);
                if ($pid < 1) {
                    continue;
                }
                if (!isset($respuestasGuardadas[$pid]) || trim((string) ($respuestasGuardadas[$pid]['estado'] ?? '')) === '') {
                    $primerPendienteIdx = $idx;
                    break;
                }
            }
            if ($primerPendienteIdx !== null) {
                $startStepIndex = ($skipPasoCabecera ? 1 : 1) + $primerPendienteIdx;
            } else {
                // Todas respondidas, saltar a sección final.
                $startStepIndex = ($skipPasoCabecera ? 1 : 1) + count($puntos);
            }
        }

        $this->view('Checklist.index', [
            'titulo' => 'MecaQuick - Checklist de mantenimiento',
            'puntos' => $puntos,
            'totalPuntos' => count($puntos),
            'tokenInicial' => $tokenInicial,
            'redirectAprendizAlFinalizar' => $redirectAprendizAlFinalizar,
            'cabeceraPrecargada' => $cabeceraPrecargada,
            'skipPasoCabecera' => $skipPasoCabecera,
            'respuestasGuardadas' => $respuestasGuardadas,
            'startStepIndex' => $startStepIndex,
        ]);
    }

    /**
     * Guarda el avance por paso cuando se pulsa "Siguiente".
     */
    public function guardarPaso(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(['ok' => false, 'message' => 'Método no permitido.'], 405);
        }

        $puntosModel = new PuntosCatalogoModel();
        $puntos = $puntosModel->listarActivos();
        $totalPuntos = count($puntos);
        $idsPuntos = array_column($puntos, 'id');

        $respuestasPermitidas = ['si', 'no', 'subsanado', 'bueno', 'regular', 'malo', 'no_aplica'];

        $token = trim((string) ($_POST['token'] ?? ''));
        if ($token === '') {
            $this->json(['ok' => false, 'message' => 'Token requerido. Inicie la recepción para obtener un checklist asignado.'], 422);
        }

        $finalizado = (int) ($_POST['finalizado'] ?? 0) === 1;
        $datos = $this->extraerDatosCabecera($_POST);
        $inspeccionModel = new InspeccionModel();
        $inspeccion = $inspeccionModel->obtenerPorToken($token);
        if ($inspeccion === null) {
            $this->json(['ok' => false, 'message' => 'Checklist no asignado. Cree la recepción para obtener un checklist válido.'], 403);
        }
        if ($inspeccion !== null) {
            $checklistDatosModel = new ChecklistDatosModel();
            $existente = $checklistDatosModel->obtenerPorInspeccionId((int) $inspeccion['id']);
            if ($existente !== null) {
                $datos = $this->fusionarConExistente($datos, $existente, $finalizado);
            }
        }
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

            $valorMedido = $_POST['valor_medido'][$puntoIdStr] ?? null;
            if ($valorMedido !== null && trim((string) $valorMedido) !== '') {
                $resultados[$puntoId]['valor_medido'] = trim((string) $valorMedido);
            }

            $observacion = $_POST['observaciones_puntos'][$puntoIdStr] ?? null;
            if ($observacion !== null && trim((string) $observacion) !== '') {
                $resultados[$puntoId]['observacion'] = trim((string) $observacion);
            }
        }

        $ultimaPregunta = (int) ($_POST['ultima_pregunta'] ?? 0);
        $ultimaPregunta = max(0, min($ultimaPregunta, $totalPuntos));

        try {
            $inspeccionId = (int) $inspeccion['id'];

            $checklistDatosModel = new ChecklistDatosModel();
            $checklistDatosModel->guardarOActualizar($inspeccionId, $datos);

            $resultadosModel = new ResultadosPuntosModel();
            $resultadosModel->guardarBatch($inspeccionId, $resultados);

            $preguntasRespondidas = $resultadosModel->contarPorInspeccion($inspeccionId);
            if ($finalizado && $preguntasRespondidas !== $totalPuntos) {
                $this->json(
                    ['ok' => false, 'message' => 'Debe completar todas las preguntas para finalizar.'],
                    422
                );
            }

            $porcentajeAvance = $totalPuntos > 0
                ? (int) round(($preguntasRespondidas / $totalPuntos) * 100)
                : 0;

            $inspeccionModel->actualizarAvance($inspeccionId, $porcentajeAvance, $finalizado);

            if ($finalizado) {
                $citaId = isset($inspeccion['cita_id']) ? (int) $inspeccion['cita_id'] : 0;
                if ($citaId > 0) {
                    $citaModel = new CitaModel();
                    $citaModel->actualizarEstado($citaId, 'completada');
                }
            }
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
     * Extrae datos del formato checklist técnico y los mapea al esquema interno.
     *
     * @return array<string, mixed>
     */
    private function extraerDatosCabecera(array $post): array
    {
        $clienteNombre = trim((string) ($post['cliente_nombre'] ?? $post['asesor'] ?? ''));
        $clienteDoc = trim((string) ($post['cliente_documento'] ?? $post['tipo_comercial_codigo'] ?? ''));
        $clienteTel = trim((string) ($post['cliente_telefono'] ?? $post['ldc'] ?? ''));
        $clienteEmail = trim((string) ($post['cliente_email'] ?? $post['vhn'] ?? ''));
        if (strlen($clienteEmail) > 20) {
            $clienteEmail = substr($clienteEmail, 0, 20);
        }
        $fechaIngreso = trim((string) ($post['fecha_ingreso'] ?? $post['fecha_servicio'] ?? ''));
        $horaIngreso = trim((string) ($post['hora_ingreso'] ?? $post['djka'] ?? ''));
        if (strlen($horaIngreso) > 20) {
            $horaIngreso = substr($horaIngreso, 0, 20);
        }

        $firmas = [
            'firma_tecnico' => trim((string) ($post['firma_tecnico'] ?? '')),
            'nombre_tecnico' => trim((string) ($post['nombre_tecnico'] ?? '')),
            'firma_cliente' => trim((string) ($post['firma_cliente'] ?? '')),
        ];
        $notaMantenimiento = json_encode($firmas, JSON_THROW_ON_ERROR);

        return [
            'numero_orden' => trim((string) ($post['numero_orden'] ?? 'N-' . date('YmdHis'))),
            'tipo_comercial_codigo' => $clienteDoc,
            'matricula' => trim((string) ($post['matricula'] ?? '')),
            'matriculacion' => preg_match('/^\d{4}-\d{2}-\d{2}/', $fechaIngreso) ? substr($fechaIngreso, 0, 10) : null,
            'bastidor' => trim((string) ($post['bastidor'] ?? '-')),
            'ldm' => trim((string) ($post['ldm'] ?? '')),
            'djka' => $horaIngreso,
            'kilometraje' => (int) ($post['kilometraje'] ?? 0),
            'asesor' => $clienteNombre !== '' ? $clienteNombre : 'Cliente',
            'tipo_comercial_modelo' => trim((string) ($post['tipo_comercial_modelo'] ?? '')),
            'ldc' => $clienteTel,
            'vhn' => $clienteEmail,
            'fecha_servicio' => $fechaIngreso !== '' ? $fechaIngreso : date('Y-m-d'),
            'tipo_inspeccion' => trim((string) ($post['tipo_inspeccion'] ?? 'Inspección técnica')),
            'km_salida' => trim((string) ($post['km_salida'] ?? '')),
            'km_llegada' => trim((string) ($post['km_llegada'] ?? '')),
            'observaciones' => trim((string) ($post['observaciones_generales'] ?? $post['observaciones'] ?? '')),
            'nota_mantenimiento' => $notaMantenimiento,
            'fecha_firma_responsable' => null,
            'fecha_firma_control' => null,
        ];
    }

    /**
     * Fusiona datos del POST con los existentes (cuando viene de recepción y no se enviaron).
     *
     * @param array<string, mixed> $datos
     * @param array<string, mixed> $existente
     * @return array<string, mixed>
     */
    private function fusionarConExistente(array $datos, array $existente, bool $finalizado): array
    {
        $camposCabecera = [
            'numero_orden', 'tipo_comercial_codigo', 'matricula', 'matriculacion', 'bastidor',
            'ldm', 'djka', 'kilometraje', 'asesor', 'tipo_comercial_modelo', 'ldc', 'vhn',
            'fecha_servicio', 'tipo_inspeccion', 'observaciones',
        ];
        foreach ($camposCabecera as $k) {
            $v = trim((string) ($datos[$k] ?? ''));
            if ($v === '' && isset($existente[$k])) {
                $datos[$k] = $existente[$k];
            }
        }
        if (!$finalizado) {
            $notaExistente = (string) ($existente['nota_mantenimiento'] ?? '');
            if ($notaExistente !== '' && str_starts_with($notaExistente, '{"firma_tecnico"')) {
                $datos['nota_mantenimiento'] = $notaExistente;
            }
        }
        return $datos;
    }

    /**
     * @param array<string, mixed> $datos
     * @return array<string>
     */
    private function validarCabecera(array $datos, bool $finalizado): array
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

        if ($finalizado) {
            $nota = (string) ($datos['nota_mantenimiento'] ?? '');
            $firmas = json_decode($nota, true);
            if (is_array($firmas)) {
                if (trim((string) ($firmas['firma_tecnico'] ?? '')) === '') {
                    $errores[] = 'Firma del técnico es obligatoria.';
                }
                if (trim((string) ($firmas['nombre_tecnico'] ?? '')) === '') {
                    $errores[] = 'Nombre del técnico es obligatorio.';
                }
                if (trim((string) ($firmas['firma_cliente'] ?? '')) === '') {
                    $errores[] = 'Firma del cliente es obligatoria.';
                }
            } else {
                $errores[] = 'Firmas incompletas.';
            }
        }

        return $errores;
    }
}
