<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Asesores') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--usuarios">
        <section class="panel panel--usuarios">
            <div class="usuarios__header">
                <h1 class="panel__title">Gestión Asesores</h1>
                <a href="/usuarios/crear?rol=asesor_servicio" class="btn btn--primary">Crear asesor</a>
            </div>
            <p class="panel__intro">Usuarios con rol asesor de servicio. Las credenciales se envían por correo al crearlos.</p>

            <?php if (empty($usuarios)): ?>
                <p class="usuarios__empty">No hay asesores registrados. <a href="/usuarios/crear?rol=asesor_servicio">Crear uno</a>.</p>
            <?php else: ?>
                <table class="usuarios__table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['nombre']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= (int) $u['activo'] === 1 ? 'Activo' : 'Inactivo' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="usuarios__links">
                <a href="/usuarios/instructores" class="btn btn--secondary">Gestión Instructores</a>
                <a href="/usuarios/aprendices" class="btn btn--secondary">Gestión Aprendices</a>
                <a href="/dashboard" class="btn btn--secondary">Volver al panel</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
