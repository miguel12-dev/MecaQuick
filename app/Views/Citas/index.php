<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Citas') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--usuarios">
        <section class="panel panel--usuarios">
            <h1 class="panel__title">Todas las citas</h1>
            <p class="panel__intro">Listado de citas sin filtro de fecha. Solo visible para administrador.</p>

            <?php if (empty($citas ?? [])): ?>
                <p class="usuarios__empty">No hay citas registradas.</p>
            <?php else: ?>
                <table class="usuarios__table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Vehículo</th>
                            <th>Placa</th>
                            <th>Estado</th>
                            <th>Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citas as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['fecha'] ?? '') ?></td>
                                <td><?= htmlspecialchars(trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''))) ?></td>
                                <td><?= htmlspecialchars($c['documento'] ?? '') ?></td>
                                <td><?= htmlspecialchars(trim(($c['marca'] ?? '') . ' ' . ($c['modelo'] ?? '') . ' ' . ($c['anio'] ?? ''))) ?></td>
                                <td><?= htmlspecialchars($c['placa'] ?? '') ?></td>
                                <td><?= htmlspecialchars($c['estado'] ?? '') ?></td>
                                <td><?= htmlspecialchars($c['created_at'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="usuarios__links">
                <a href="/dashboard" class="btn btn--secondary">Volver al panel</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
