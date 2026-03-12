<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Panel') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <?php require ROOT_PATH . '/app/Views/partials/styles.php'; ?>
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <header class="dashboard__header">
                <h1 class="panel__title">Panel de usuario</h1>
                <p class="panel__intro">
                    Bienvenido, <strong><?= htmlspecialchars($usuario['nombre'] ?? '') ?></strong>.
                    <span class="dashboard__rol">Rol: <?= htmlspecialchars($usuario['rol'] ?? '') ?></span>
                </p>
            </header>

            <section class="dashboard__grid" aria-label="Accesos rápidos del panel">
                <?php if (\App\Services\AuthService::puedeVerModuloVehiculo()): ?>
                    <article class="dashboard-card">
                        <h2 class="dashboard-card__title">Recepción y checklist de vehículos</h2>
                        <p class="dashboard-card__text">
                            Gestiona el ingreso de vehículos al taller y el checklist inicial de revisión.
                        </p>
                        <div class="dashboard-card__actions">
                            <a href="/recepcion" class="btn btn--primary">Ir a Recepción</a>
                            <a href="/checklist" class="btn btn--secondary">Checklist vehículos</a>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if (($usuario['rol'] ?? '') === 'aprendiz'): ?>
                    <article class="dashboard-card">
                        <h2 class="dashboard-card__title">Módulo de mantenimiento</h2>
                        <p class="dashboard-card__text">
                            Consulta y registra las inspecciones y actividades de mantenimiento que tengas asignadas.
                        </p>
                        <div class="dashboard-card__actions">
                            <a href="/mantenimiento" class="btn btn--primary">Abrir módulo</a>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if (in_array($usuario['rol'] ?? '', ['admin', 'instructor'], true)): ?>
                    <article class="dashboard-card">
                        <h2 class="dashboard-card__title">Panel de revisiones</h2>
                        <p class="dashboard-card__text">
                            Supervisa el estado de las revisiones mecánicas y el avance de las inspecciones.
                        </p>
                            <div class="dashboard-card__actions">
                            <a href="/checklist/panel" class="btn btn--primary">Ver panel de revisiones</a>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if (($usuario['rol'] ?? '') === 'admin'): ?>
                    <article class="dashboard-card">
                        <h2 class="dashboard-card__title">Gestión de citas</h2>
                        <p class="dashboard-card__text">
                            Revisa todas las citas programadas en el sistema.
                        </p>
                        <div class="dashboard-card__actions">
                            <a href="/citas" class="btn btn--primary">Ver todas las citas</a>
                        </div>
                    </article>

                    <article class="dashboard-card">
                        <h2 class="dashboard-card__title">Gestión de aprendices</h2>
                        <p class="dashboard-card__text">
                            Administra la información de los aprendices vinculados al centro de formación.
                        </p>
                        <div class="dashboard-card__actions">
                            <a href="/usuarios/aprendices" class="btn btn--primary">Gestionar aprendices</a>
                        </div>
                    </article>

                    <article class="dashboard-card">
                        <h2 class="dashboard-card__title">Gestión de instructores</h2>
                        <p class="dashboard-card__text">
                            Configura y actualiza los instructores responsables de las revisiones mecánicas.
                        </p>
                        <div class="dashboard-card__actions">
                            <a href="/usuarios/instructores" class="btn btn--primary">Gestionar instructores</a>
                        </div>
                    </article>

                    <article class="dashboard-card">
                        <h2 class="dashboard-card__title">Gestión de asesores</h2>
                        <p class="dashboard-card__text">
                            Administra los asesores de servicio encargados de la recepción de vehículos.
                        </p>
                        <div class="dashboard-card__actions">
                            <a href="/usuarios/asesores" class="btn btn--primary">Gestionar asesores</a>
                        </div>
                    </article>
                <?php endif; ?>

            </section>

            <section class="dashboard__info" aria-label="Descripción de permisos por rol">
                <?php if (($usuario['rol'] ?? '') === 'admin'): ?>
                    <p>Como administrador puedes ver todas las citas, gestionar usuarios y acceder a todos los módulos y checklist.</p>
                <?php elseif (($usuario['rol'] ?? '') === 'instructor'): ?>
                    <p>Como instructor (mecánico líder) puedes supervisar inspecciones, gestionar revisiones y acceder al checklist.</p>
                <?php elseif (($usuario['rol'] ?? '') === 'asesor_servicio'): ?>
                    <p>Como asesor de servicio puedes realizar recepciones, gestionar el contacto con el usuario y acceder al checklist de vehículos.</p>
                <?php else: ?>
                    <p>Como aprendiz (mecánico) puedes realizar inspecciones y registrar la información en el checklist de vehículos.</p>
                <?php endif; ?>
            </section>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
