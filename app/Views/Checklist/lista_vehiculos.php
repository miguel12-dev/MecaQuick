<?php
$vehiculos = $vehiculos ?? [];
$usuario = $usuario ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'MecaQuick') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require dirname(__DIR__, 2) . '/Views/partials/header.php'; ?>

    <main class="layout layout--form-page">
        <section class="panel panel--form">
            <h1 class="panel__title">Checklist técnico – vehículos</h1>
            <p class="panel__intro">
                Seleccione un vehículo pendiente o con revisión completa para continuar el checklist, o inicie una nueva revisión.
            </p>

            <div class="checklist-lista__actions">
                <a href="/checklist/nuevo" class="btn btn--primary">
                    <i class="fas fa-plus-circle"></i>
                    Nueva revisión
                </a>
            </div>

            <?php if (empty($vehiculos)): ?>
                <p class="checklist-lista__vacio">
                    No hay vehículos pendientes ni con revisión completa registrados.
                    <?php if ($usuario === null): ?>
                        <a href="/login">Inicie sesión</a> para ver sus inspecciones o cree una <a href="/checklist/nuevo">nueva revisión</a>.
                    <?php else: ?>
                        Cree una nueva revisión desde <a href="/recepcion">recepción</a> o mediante el botón anterior.
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <div class="checklist-lista__tabla-wrap">
                    <table class="panel-table checklist-lista__tabla" aria-label="Vehículos pendientes y en revisión completa">
                        <thead>
                            <tr>
                                <th scope="col">Placa</th>
                                <th scope="col">Modelo</th>
                                <th scope="col">Encargado</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Avance</th>
                                <th scope="col">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehiculos as $v): ?>
                                <?php
                                $fecha = isset($v['inicio_at']) ? date('d/m/Y H:i', strtotime($v['inicio_at'])) : '—';
                                $estado = $v['estado'] ?? 'en_proceso';
                                $estadoLabel = $estado === 'finalizada' ? 'Revisión completa' : 'Pendiente';
                                $estadoClase = $estado === 'finalizada' ? 'checklist-lista__estado--completa' : 'checklist-lista__estado--pendiente';
                                $urlChecklist = '/checklist?token=' . urlencode($v['token'] ?? '');
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($v['placa'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($v['modelo'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($v['encargado'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($fecha) ?></td>
                                    <td><span class="checklist-lista__estado <?= $estadoClase ?>"><?= htmlspecialchars($estadoLabel) ?></span></td>
                                    <td><?= (int) ($v['porcentaje_avance'] ?? 0) ?>%</td>
                                    <td>
                                        <a href="<?= htmlspecialchars($urlChecklist) ?>" class="btn btn--primary btn--small">
                                            <i class="fas fa-clipboard-list"></i>
                                            Ir al checklist
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Registro de checklist de revisión mecánica</span>
    </footer>
</body>
</html>
