<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Módulo de mantenimiento') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <h1 class="panel__title">Módulo de mantenimiento</h1>
            <p class="panel__intro">
                Bienvenido, <strong><?= htmlspecialchars($usuario['nombre'] ?? '') ?></strong>.
                Elige una opción para continuar.
            </p>

            <div class="dashboard__actions dashboard__actions--grid">
                <a href="/mantenimiento/aprendizaje" class="btn btn--primary">Aprendizaje</a>
                <a href="/mantenimiento/mis-revisiones" class="btn btn--primary">Ver mis revisiones</a>
                <a href="/mantenimiento/crear" class="btn btn--primary">Crear nueva checklist / mantenimiento</a>
            </div>

            <div class="dashboard__info">
                <p>Desde aquí puedes consultar el contenido formativo, ver los vehículos o checklists que ya has realizado y crear un nuevo mantenimiento. Al crear uno nuevo, el tutor del módulo será asignado automáticamente.</p>
            </div>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
