<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ConfiguracionModel;

final class MailService
{
    /**
     * Construye una instancia configurada de PHPMailer.
     *
     * @return object
     */
    private function buildMailer(): object
    {
        $basePath = ROOT_PATH . '/libs/PHPMailer/src';

        $mainFile = $basePath . '/PHPMailer.php';
        $smtpFile = $basePath . '/SMTP.php';
        $excFile  = $basePath . '/Exception.php';

        if (!is_file($mainFile) || !is_file($smtpFile) || !is_file($excFile)) {
            throw new \RuntimeException('PHPMailer no está instalado en /libs/PHPMailer.');
        }

        require_once $mainFile;
        require_once $smtpFile;
        require_once $excFile;

        $mailerClass = '\\PHPMailer\\PHPMailer\\PHPMailer';
        $mail = new $mailerClass(true);
        $mail->isSMTP();

        $host   = (string) (ConfiguracionModel::get('smtp_host') ?? '');
        $port   = (int) (ConfiguracionModel::get('smtp_port') ?? 587);
        $user   = (string) (ConfiguracionModel::get('smtp_user') ?? '');
        $pass   = (string) (ConfiguracionModel::get('smtp_pass') ?? '');
        $from   = trim((string) (ConfiguracionModel::get('smtp_from') ?? $user));

        if ($from === '' && $user === '') {
            throw new \RuntimeException('Configuración SMTP incompleta: smtp_from y smtp_user están vacíos.');
        }

        $mail->Host       = $host;
        $mail->Port       = $port;
        $mail->SMTPAuth   = $user !== '' && $pass !== '';
        $mail->Username   = $user;
        $mail->Password   = $pass;
        $mail->CharSet    = 'UTF-8';
        $mail->isHTML(true);

        if ($port === 465) {
            $mail->SMTPSecure = 'ssl';
        } else {
            $mail->SMTPSecure = 'tls';
        }

        $fromEmail = $from !== '' ? $from : $user;
        $mail->setFrom($fromEmail, (string) (ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick'));

        return $mail;
    }

    public function enviarConfirmacionCita(string $destinatario, array $cita): bool
    {
        if ($destinatario === '') {
            return false;
        }

        try {
            $mail = $this->buildMailer();
        } catch (\Throwable) {
            // SMTP no configurado o PHPMailer falla: se omite el envío sin romper el flujo.
            return false;
        }

        $nombreSistema = (string) (ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick');
        $zonaReunion   = (string) (ConfiguracionModel::get('zona_reunion') ?? 'CTA Caquetá, vía al aeropuerto');
        $horarioAtencion = (string) (ConfiguracionModel::get('horario_atencion') ?? '07:00 - 11:30');

        $mail->addAddress($destinatario, $cita['nombre'] . ' ' . $cita['apellido']);
        $mail->Subject = $nombreSistema . ' - Confirmación de cita';

        $fechaRegistro = isset($cita['created_at'])
            ? date('d/m/Y H:i', strtotime($cita['created_at']))
            : date('d/m/Y H:i');

        $vehiculoLinea = trim(
            $cita['marca'] . ' ' . ($cita['modelo'] ?? '') . ($cita['anio'] ? ' (' . $cita['anio'] . ')' : '')
        );
        if ($vehiculoLinea === '') {
            $vehiculoLinea = 'Placa ' . $cita['placa'];
        } else {
            $vehiculoLinea .= ' - Placa ' . $cita['placa'];
        }

        $body = sprintf(
            '<h2>Confirmación de cita de revisión mecánica</h2>
             <p>Hola <strong>%s %s</strong>,</p>
             <p>Tu solicitud de cita ha sido registrada correctamente. A continuación los datos que debes tener en cuenta:</p>
             <table style="border-collapse: collapse; margin: 1rem 0;">
               <tr><td style="padding: 0.35rem 0.5rem 0.5rem 0; font-weight: bold;">Fecha de la revisión:</td><td>%s</td></tr>
               <tr><td style="padding: 0.35rem 0.5rem 0.5rem 0; font-weight: bold;">Horario de atención:</td><td>%s</td></tr>
               <tr><td style="padding: 0.35rem 0.5rem 0.5rem 0; font-weight: bold;">Zona de reunión:</td><td>%s</td></tr>
               <tr><td style="padding: 0.35rem 0.5rem 0.5rem 0; font-weight: bold;">Vehículo:</td><td>%s</td></tr>
               <tr><td style="padding: 0.35rem 0.5rem 0.5rem 0; font-weight: bold;">Token de confirmación:</td><td><code>%s</code></td></tr>
               <tr><td style="padding: 0.35rem 0.5rem 0.5rem 0; font-weight: bold;">Registro realizado el:</td><td>%s</td></tr>
             </table>
             <p><strong>Importante:</strong> Presenta este correo o tu token el día de la revisión. La atención es en <strong>%s</strong> en el horario indicado.</p>
             <p>Saludos cordiales,<br>%s</p>',
            htmlspecialchars($cita['nombre'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($cita['apellido'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($horarioAtencion, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($zonaReunion, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($vehiculoLinea, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($cita['token'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($fechaRegistro, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($zonaReunion, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($nombreSistema, ENT_QUOTES, 'UTF-8')
        );

        $mail->Body = $body;

        try {
            return $mail->send();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Envía al usuario sus credenciales de acceso (correo y contraseña) tras ser dado de alta.
     */
    public function enviarCredencialesUsuario(
        string $destinatario,
        string $nombre,
        string $email,
        string $passwordPlano,
        string $rol
    ): bool {
        if ($destinatario === '') {
            return false;
        }

        try {
            $mail = $this->buildMailer();
        } catch (\RuntimeException) {
            return false;
        }

        $nombreSistema = (string) (ConfiguracionModel::get('nombre_sistema') ?? 'MecaQuick');
        $rolEtiqueta = $rol === 'instructor' ? 'Instructor' : 'Aprendiz';
        $urlLogin = (isset($_SERVER['HTTP_HOST']) ? ('http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 's' : '') . '://' . $_SERVER['HTTP_HOST']) : '') . '/login';

        $mail->addAddress($destinatario, $nombre);
        $mail->Subject = $nombreSistema . ' - Credenciales de acceso';

        $body = sprintf(
            '<h2>Credenciales de acceso</h2>
             <p>Hola <strong>%s</strong>,</p>
             <p>Se ha creado tu cuenta en <strong>%s</strong> con el rol de <strong>%s</strong>. Puedes acceder con los siguientes datos:</p>
             <table style="border-collapse: collapse; margin: 1rem 0;">
               <tr><td style="padding: 0.35rem 0.5rem 0.5rem 0; font-weight: bold;">Correo:</td><td>%s</td></tr>
               <tr><td style="padding: 0.35rem 0.5rem 0.5rem 0; font-weight: bold;">Contraseña:</td><td><code>%s</code></td></tr>
               <tr><td style="padding: 0.35rem 0.5rem 0.5rem 0; font-weight: bold;">URL de acceso:</td><td><a href="%s">%s</a></td></tr>
             </table>
             <p><strong>Recomendación:</strong> Cambia tu contraseña tras el primer acceso si el sistema lo permite.</p>
             <p>Saludos cordiales,<br>%s</p>',
            htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($nombreSistema, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($rolEtiqueta, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($passwordPlano, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($urlLogin, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($urlLogin, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($nombreSistema, ENT_QUOTES, 'UTF-8')
        );

        $mail->Body = $body;

        try {
            return $mail->send();
        } catch (\Throwable) {
            return false;
        }
    }
}

