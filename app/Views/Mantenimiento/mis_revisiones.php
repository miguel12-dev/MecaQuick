<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Mis revisiones') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <h1 class="panel__title">Mis revisiones</h1>
            <p class="panel__intro">
                Vehículos y checklists en los que has participado como responsable o ayudante.
            </p>

            <div class="dashboard__actions">
                <a href="/mantenimiento" class="btn btn--secondary">Volver al módulo</a>
            </div>

            <?php if (empty($revisiones)): ?>
                <p class="dashboard__info">No tienes revisiones registradas.</p>
            <?php else: ?>
                <table class="panel-table">
                    <thead>
                        <tr>
                            <th>Placa / Matrícula</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Avance</th>
                            <th>Rol</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revisiones as $r): ?>
                            <?php
                            $fecha = isset($r['inicio_at']) ? date('d/m/Y H:i', strtotime($r['inicio_at'])) : '—';
                            $estado = $r['estado'] ?? 'en_proceso';
                            $estadoLabel = $estado === 'finalizada' ? 'Finalizada' : 'En proceso';
                            $esResponsable = !empty($r['es_responsable']);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($r['placa'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($fecha) ?></td>
                                <td><?= htmlspecialchars($estadoLabel) ?></td>
                                <td><?= (int) ($r['porcentaje_avance'] ?? 0) ?>%</td>
                                <td><?= $esResponsable ? 'Responsable' : 'Ayudante' ?></td>
                                <td>
                                    <a href="/mantenimiento/revision/<?= (int) $r['id'] ?>" class="btn btn--secondary btn--small">Ver detalle</a>
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
