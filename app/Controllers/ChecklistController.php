<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ChecklistDatosModel;
use App\Models\InspeccionModel;
use App\Models\PuntosCatalogoModel;
use App\Models\ResultadosPuntosModel;
use Core\BaseController;
use Throwable;

final class ChecklistController extends BaseController
{
    /**
     * Formulario público del checklist para pruebas.
     */
    public function index(): void
    {
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

        $this->view('Checklist.index', [
            'titulo' => 'MecaQuick - Checklist de mantenimiento',
            'puntos' => $puntos,
            'totalPuntos' => count($puntos),
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

            $valorMedido = $_POST['valor_medido'][$puntoIdStr] ?? null;
            if ($valorMedido !== null && trim((string) $valorMedido) !== '') {
                $resultados[$puntoId]['valor_medido'] = trim((string) $valorMedido);
            }
        }

        $preguntasRespondidas = count($resultados);
        $ultimaPregunta = (int) ($_POST['ultima_pregunta'] ?? 0);
        $ultimaPregunta = max(0, min($ultimaPregunta, $totalPuntos));

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

        $this->json([
            'ok' => true,
            'token' => $token,
            'progress' => [
                'answered' => $preguntasRespondidas,
                'total' => $totalPuntos,
                'percentage' => $porcentajeAvance,
            ],
        ]);
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
    private function validarCabecera(array $datos, bool $finalizado): array
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

        if ($finalizado) {
            $camposRequeridos['km_salida'] = 'Salida (km)';
            $camposRequeridos['km_llegada'] = 'Llegada (km)';
            $camposRequeridos['nota_mantenimiento'] = 'Nota de mantenimiento';
            $camposRequeridos['fecha_firma_responsable'] = 'Fecha/firma (responsable)';
            $camposRequeridos['fecha_firma_control'] = 'Fecha/firma (control final)';
        }

        foreach ($camposRequeridos as $campo => $etiqueta) {
            $v = trim((string) ($datos[$campo] ?? ''));
            if ($v === '') {
                $errores[] = $etiqueta . ' es obligatorio.';
                continue;
            }
            if (in_array($campo, ['kilometraje', 'km_salida', 'km_llegada'], true)) {
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

        if ($finalizado) {
            foreach (['fecha_firma_responsable', 'fecha_firma_control'] as $f) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($datos[$f] ?? ''))) {
                    $errores[] = ($f === 'fecha_firma_responsable' ? 'Fecha/firma (responsable)' : 'Fecha/firma (control final)') . ' no válida.';
                }
            }
        }

        return $errores;
    }
}
