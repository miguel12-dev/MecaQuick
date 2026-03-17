<?php
$citas = $citas ?? [];
$fechaHoy = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Citas del día') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <h1 class="panel__title">Citas del día</h1>
            <p class="panel__intro">
                Seleccione una cita para iniciar la recepción del vehículo. Los datos del cliente y del vehículo se cargarán automáticamente en el formulario.
            </p>

            <div class="dashboard__actions" style="margin-bottom: 1rem;">
                <a href="/recepcion" class="btn btn--secondary">Volver al módulo</a>
                <a href="/recepcion/crear/sin-cita" class="btn btn--primary">
                    <i class="fas fa-plus"></i> Recepción sin cita vinculada
                </a>
            </div>

            <?php if (empty($citas)): ?>
                <p class="dashboard__info">No hay citas programadas para hoy (<?= htmlspecialchars($fechaHoy) ?>). Puede crear una recepción manual sin vincular a una cita.</p>
            <?php else: ?>
                <table class="panel-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Placa</th>
                            <th>Vehículo</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citas as $c): ?>
                            <?php
                            $clienteNombre = trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
                            $vehiculoLinea = trim(($c['marca'] ?? '') . ' ' . ($c['modelo'] ?? ''));
                            if ($vehiculoLinea === '') {
                                $vehiculoLinea = '—';
                            }
                            $estadoLabel = $c['estado'] === 'confirmada' ? 'Confirmada' : 'Pendiente';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($clienteNombre) ?></td>
                                <td><?= htmlspecialchars($c['documento'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($c['placa'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($vehiculoLinea) ?></td>
                                <td><?= htmlspecialchars($estadoLabel) ?></td>
                                <td>
                                    <a href="/recepcion/crear/<?= (int) $c['id'] ?>" class="btn btn--primary btn--small">
                                        <i class="fas fa-clipboard-check"></i> Ver esta recepción
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
