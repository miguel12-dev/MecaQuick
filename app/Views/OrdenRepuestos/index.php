<?php
$ordenes = $ordenes ?? [];
$guardado = isset($_GET['guardado']) && $_GET['guardado'] === '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Orden de repuestos') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <h1 class="panel__title">Orden de repuestos</h1>
            <p class="panel__intro">
                Gestión de órdenes de repuestos. Puede crear una nueva orden o continuar desde una inspección finalizada.
            </p>

            <?php if ($guardado): ?>
                <div class="alert alert--success" role="status">Orden guardada correctamente.</div>
            <?php endif; ?>

            <div class="dashboard__actions" style="margin-bottom: 1rem;">
                <a href="/orden-repuestos/crear" class="btn btn--primary">
                    <i class="fas fa-plus"></i> Nueva orden de repuestos
                </a>
                <a href="/recepcion" class="btn btn--secondary">Volver al módulo de recepción</a>
            </div>

            <?php if (empty($ordenes)): ?>
                <p class="dashboard__info">No hay órdenes de repuestos registradas.</p>
            <?php else: ?>
                <table class="panel-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Placa</th>
                            <th>Fecha entrada</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordenes as $o): ?>
                            <tr>
                                <td><?= (int) ($o['id'] ?? 0) ?></td>
                                <td><?= htmlspecialchars($o['cliente_nombre'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($o['placa'] ?? '—') ?></td>
                                <td><?= !empty($o['fecha_entrada']) ? date('d/m/Y', strtotime($o['fecha_entrada'])) : '—' ?></td>
                                <td>$ <?= number_format((float) ($o['total'] ?? 0), 0, ',', '.') ?></td>
                                <td>
                                    <a href="/orden-repuestos/ver/<?= (int) $o['id'] ?>" class="btn btn--secondary btn--small">
                                        <i class="fas fa-eye"></i> Ver
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
