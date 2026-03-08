<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Panel') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <h1 class="panel__title">Panel de usuario</h1>
            <p class="panel__intro">
                Bienvenido, <strong><?= htmlspecialchars($usuario['nombre'] ?? '') ?></strong>.
                Rol: <span class="dashboard__rol"><?= htmlspecialchars($usuario['rol'] ?? '') ?></span>.
            </p>

            <div class="dashboard__actions">
                <a href="/checklist" class="btn btn--primary">Checklist de vehículos</a>
                <?php if (in_array($usuario['rol'] ?? '', ['admin', 'instructor'], true)): ?>
                    <a href="/checklist/panel" class="btn btn--primary">Panel de revisiones</a>
                <?php endif; ?>
                <?php if (($usuario['rol'] ?? '') === 'admin'): ?>
                    <a href="/usuarios/aprendices" class="btn btn--primary">Gestión Aprendices</a>
                    <a href="/usuarios/instructores" class="btn btn--primary">Gestión Instructores</a>
                <?php endif; ?>
                <a href="/logout" class="btn btn--secondary">Cerrar sesión</a>
            </div>

            <div class="dashboard__info">
                <?php if (($usuario['rol'] ?? '') === 'admin'): ?>
                    <p>Como administrador puedes gestionar instructores y aprendices, y acceder al checklist.</p>
                <?php elseif (($usuario['rol'] ?? '') === 'instructor'): ?>
                    <p>Como instructor puedes supervisar inspecciones y acceder al checklist de mantenimiento.</p>
                <?php else: ?>
                    <p>Como aprendiz puedes realizar inspecciones y acceder al checklist de vehículos.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
