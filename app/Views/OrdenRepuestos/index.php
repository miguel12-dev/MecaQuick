<?php
$ordenes = $ordenes ?? [];
$usuario = $usuario ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Órdenes de repuestos') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <p class="panel__back">
                <a href="/dashboard" class="btn btn--secondary">Volver al panel</a>
            </p>
            <h1 class="panel__title">Orden de repuestos</h1>
            <p class="panel__intro">
                Órdenes creadas al finalizar el checklist. Complete los datos y la tabla de ítems.
            </p>

            <?php if (empty($ordenes)): ?>
                <p class="panel__text">
                    No hay órdenes de repuestos. Al finalizar un checklist, puede optar por crear una orden de repuestos.
                </p>
                <div class="dashboard__actions">
                    <a href="/recepcion" class="btn btn--primary">Ir a recepción</a>
                    <a href="/checklist" class="btn btn--secondary">Checklist de vehículos</a>
                </div>
            <?php else: ?>
                <div class="panel-table-wrap">
                    <table class="panel-table" aria-label="Órdenes de repuestos">
                        <thead>
                            <tr>
                                <th scope="col">Placa</th>
                                <th scope="col">Modelo</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Fecha entrada</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordenes as $o): ?>
                                <?php
                                $fecha = !empty($o['fecha_entrada']) ? date('d/m/Y', strtotime($o['fecha_entrada'])) : '—';
                                $estado = $o['estado'] ?? 'pendiente';
                                $estadoLabel = $estado === 'completada' ? 'Completada' : 'Pendiente';
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($o['placa'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($o['modelo'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($o['cliente_nombre'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($fecha) ?></td>
                                    <td><?= htmlspecialchars($estadoLabel) ?></td>
                                    <td>
                                        <a href="/orden-repuestos/editar/<?= (int) $o['id'] ?>" class="btn btn--primary btn--small">
                                            <i class="fas fa-edit"></i>
                                            <?= $estado === 'completada' ? 'Ver/Editar' : 'Completar' ?>
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
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
