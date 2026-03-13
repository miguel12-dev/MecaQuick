<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Confirmación de cita') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--centered">
        <section class="panel panel--success">
            <h1 class="panel__title">Cita registrada correctamente</h1>
            <p class="panel__text">
                Hemos registrado tu solicitud de revisión mecánica. A continuación encontrarás el resumen de tu turno.
            </p>

            <div class="summary">
                <h2 class="summary__title">Datos del turno</h2>
                <dl class="summary__list">
                    <div>
                        <dt>Fecha de revisión</dt>
                        <dd><?= htmlspecialchars($cita['fecha']) ?></dd>
                    </div>
                    <div>
                        <dt>Token de confirmación</dt>
                        <dd><code><?= htmlspecialchars($cita['token']) ?></code></dd>
                    </div>
                    <div>
                        <dt>Registro realizado el</dt>
                        <dd><?= isset($cita['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($cita['created_at']))) : '-' ?></dd>
                    </div>
                    <div>
                        <dt>Estado</dt>
                        <dd><?= htmlspecialchars($cita['estado']) ?></dd>
                    </div>
                </dl>

                <h2 class="summary__title">Datos del solicitante</h2>
                <dl class="summary__list">
                    <div>
                        <dt>Nombre</dt>
                        <dd><?= htmlspecialchars($cita['nombre'] . ' ' . $cita['apellido']) ?></dd>
                    </div>
                    <div>
                        <dt>Documento</dt>
                        <dd><?= htmlspecialchars($cita['documento']) ?></dd>
                    </div>
                    <div>
                        <dt>Teléfono</dt>
                        <dd><?= htmlspecialchars($cita['telefono'] ?? '-') ?></dd>
                    </div>
                    <div>
                        <dt>Correo electrónico</dt>
                        <dd><?= htmlspecialchars($cita['email']) ?></dd>
                    </div>
                </dl>

                <h2 class="summary__title">Datos del vehículo</h2>
                <dl class="summary__list">
                    <div>
                        <dt>Placa</dt>
                        <dd><?= htmlspecialchars($cita['placa']) ?></dd>
                    </div>
                    <div>
                        <dt>Marca<?= (!empty($cita['modelo']) && $cita['modelo'] !== '-') ? ' / Modelo' : '' ?></dt>
                        <dd><?= htmlspecialchars(trim($cita['marca'] . ((!empty($cita['modelo']) && $cita['modelo'] !== '-') ? ' ' . $cita['modelo'] : ''))) ?></dd>
                    </div>
                    <div>
                        <dt>Año</dt>
                        <dd><?= htmlspecialchars($cita['anio'] ?? '-') ?></dd>
                    </div>
                </dl>
            </div>

            <p class="panel__text">
                Recibirás un correo con esta misma información si el envío SMTP está correctamente configurado.
            </p>

            <div class="form__actions">
                <a href="/home/formulario" class="btn btn--secondary">Registrar otra cita</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>

