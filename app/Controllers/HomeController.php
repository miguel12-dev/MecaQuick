<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\BaseController;
use App\Models\ConfiguracionModel;
use App\Models\FechaDisponibleModel;
use App\Models\ClienteModel;
use App\Models\VehiculoModel;
use App\Models\CitaModel;
use App\Services\MailService;

/**
 * Controlador de la página principal (landing).
 */
class HomeController extends BaseController
{
    /**
     * Página principal (landing): explica el proceso y enlaza al formulario.
     */
    public function index(): void
    {
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';
        $this->view('Home.landing', [
            'titulo'        => $nombreSistema,
            'nombreSistema' => $nombreSistema,
        ]);
    }

    /**
     * Formulario de solicitud de cita.
     */
    public function formulario(): void
    {
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';
        $sistemaActivo = (bool) (ConfiguracionModel::get('sistema_activo') ?? true);
        $maxCuposDia   = (int) (ConfiguracionModel::get('max_cupos_dia') ?? 4);

        $fechaModel = new FechaDisponibleModel();
        $fechasBrutas = $fechaModel->listarConOcupacion();

        $fechas = [];
        foreach ($fechasBrutas as $fila) {
            $limite = min((int) $fila['max_cupos'], $maxCuposDia);
            $usadas = (int) $fila['citas_usadas'];
            $disponible = $fila['activa'] && $usadas < $limite;

            if (!$disponible) {
                continue;
            }

            $fechas[] = [
                'id'    => (int) $fila['id'],
                'fecha' => $fila['fecha'],
            ];
        }

        $this->view('Home.formulario', [
            'titulo'         => $nombreSistema . ' - Solicitar cita',
            'nombreSistema'  => $nombreSistema,
            'sistemaActivo'  => $sistemaActivo,
            'fechas'         => $fechas,
            'errores'        => [],
            'old'            => [],
        ]);
    }

    public function registrar(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/');
        }

        $nombre    = trim((string) ($_POST['nombre'] ?? ''));
        $apellido  = trim((string) ($_POST['apellido'] ?? ''));
        $documento = trim((string) ($_POST['documento'] ?? ''));
        $telefono  = trim((string) ($_POST['telefono'] ?? ''));
        $email     = trim((string) ($_POST['email'] ?? ''));
        $placa     = strtoupper(trim((string) ($_POST['placa'] ?? '')));
        $marca     = trim((string) ($_POST['marca'] ?? ''));
        $modelo    = trim((string) ($_POST['modelo'] ?? ''));
        $fechaId   = (int) ($_POST['fecha_id'] ?? 0);
        $observaciones = trim((string) ($_POST['observaciones'] ?? ''));

        $errores = [];

        if ($nombre === '' || $apellido === '' || $documento === '' || $email === '' || $placa === '' || $marca === '') {
            $errores[] = 'Nombre, apellido, documento, correo, placa y marca son obligatorios.';
        }

        if ($placa !== '' && !preg_match('/^[A-Z]{3}[0-9]{3}$/', $placa)) {
            $errores[] = 'La placa debe tener 3 letras y 3 números (ej: ABC123).';
        }

        if ($nombre !== '' && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/', $nombre)) {
            $errores[] = 'El nombre solo puede contener letras y espacios.';
        }

        if ($apellido !== '' && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/', $apellido)) {
            $errores[] = 'El apellido solo puede contener letras y espacios.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no tiene un formato válido.';
        }

        if ($fechaId <= 0) {
            $errores[] = 'Debe seleccionar una fecha válida para la revisión.';
        }

        $maxCuposDia = (int) (ConfiguracionModel::get('max_cupos_dia') ?? 4);
        $fechaModel  = new FechaDisponibleModel();

        if (!$fechaModel->tieneCupo($fechaId, $maxCuposDia)) {
            $errores[] = 'La fecha seleccionada ya no tiene cupos disponibles.';
        }

        if ($errores !== []) {
            $this->recargarFormularioConErrores($errores);
            return;
        }

        $clienteModel  = new ClienteModel();
        $vehiculoModel = new VehiculoModel();
        $citaModel     = new CitaModel();

        $clienteId = $clienteModel->obtenerIdOCrear(
            $nombre,
            $apellido,
            $documento,
            $telefono,
            $email
        );

        $vehiculoId = $vehiculoModel->obtenerIdOCrear(
            $placa,
            $marca,
            $modelo !== '' ? $modelo : '-',
            $clienteId
        );

        $token = bin2hex(random_bytes(32));

        $citaId = $citaModel->crear(
            $token,
            $fechaId,
            $vehiculoId,
            $observaciones !== '' ? $observaciones : null
        );

        // Enviar correo de confirmación (si la configuración SMTP está correcta)
        $citaCompleta = $citaModel->obtenerPorToken($token);
        if ($citaCompleta !== null) {
            $mailService = new MailService();
            $enviado = $mailService->enviarConfirmacionCita($email, $citaCompleta);
            if ($enviado) {
                $citaModel->marcarCorreoEnviado($citaId);
            }
        }

        $this->redirect('/home/exito/' . $token);
    }

    public function exito(string $token): void
    {
        $citaModel = new CitaModel();
        $cita = $citaModel->obtenerPorToken($token);

        if ($cita === null) {
            $this->redirect('/');
        }

        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';

        $this->view('Home.exito', [
            'titulo'        => $nombreSistema . ' - Confirmación de cita',
            'nombreSistema' => $nombreSistema,
            'cita'          => $cita,
        ]);
    }

    private function recargarFormularioConErrores(array $errores): void
    {
        $nombreSistema = ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick';
        $sistemaActivo = (bool) (ConfiguracionModel::get('sistema_activo') ?? true);
        $maxCuposDia   = (int) (ConfiguracionModel::get('max_cupos_dia') ?? 4);

        $fechaModel = new FechaDisponibleModel();
        $fechasBrutas = $fechaModel->listarConOcupacion();

        $fechas = [];
        foreach ($fechasBrutas as $fila) {
            $limite = min((int) $fila['max_cupos'], $maxCuposDia);
            $usadas = (int) $fila['citas_usadas'];
            $disponible = $fila['activa'] && $usadas < $limite;

            if (!$disponible) {
                continue;
            }

            $fechas[] = [
                'id'    => (int) $fila['id'],
                'fecha' => $fila['fecha'],
            ];
        }

        $old = [
            'nombre'        => $_POST['nombre'] ?? '',
            'apellido'      => $_POST['apellido'] ?? '',
            'documento'     => $_POST['documento'] ?? '',
            'telefono'      => $_POST['telefono'] ?? '',
            'email'         => $_POST['email'] ?? '',
            'placa'         => $_POST['placa'] ?? '',
            'marca'         => $_POST['marca'] ?? '',
            'modelo'        => $_POST['modelo'] ?? '',
            'fecha_id'      => $_POST['fecha_id'] ?? '',
            'observaciones' => $_POST['observaciones'] ?? '',
        ];

        $this->view('Home.formulario', [
            'titulo'         => $nombreSistema . ' - Solicitar cita',
            'nombreSistema'  => $nombreSistema,
            'sistemaActivo'  => $sistemaActivo,
            'fechas'         => $fechas,
            'errores'        => $errores,
            'old'            => $old,
        ]);
    }
}
