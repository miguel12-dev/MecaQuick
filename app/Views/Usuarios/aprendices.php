<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Aprendices') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--usuarios">
        <section class="panel panel--usuarios">
            <div class="usuarios__header">
                <h1 class="panel__title">Gestión Aprendices</h1>
                <a href="/usuarios/crear?rol=aprendiz" class="btn btn--primary">Crear aprendiz</a>
            </div>
            <p class="panel__intro">Usuarios con rol aprendiz. Las credenciales se envían por correo al crearlos.</p>

            <?php if (empty($usuarios)): ?>
                <p class="usuarios__empty">No hay aprendices registrados. <a href="/usuarios/crear?rol=aprendiz">Crear uno</a>.</p>
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
                <a href="/dashboard" class="btn btn--secondary">Volver al panel</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
