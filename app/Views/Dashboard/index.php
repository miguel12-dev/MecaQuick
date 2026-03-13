<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Panel Administrativo') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <div class="dashboard__header">
            <div class="dashboard__header-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <h1 class="dashboard__header-title">Panel Administrativo</h1>
            <p class="dashboard__header-subtitle">Accede a las funciones principales del sistema.</p>
        </div>

        <div class="dashboard__grid">
            <?php if (($usuario['rol'] ?? '') === 'admin'): ?>
                <article class="dashboard__card">
                    <div class="dashboard__card-icon">
                        <i class="fas fa-th-list"></i>
                    </div>
                    <h2 class="dashboard__card-title">Panel de Revisiones</h2>
                    <p class="dashboard__card-description">Supervisar y revisar inspecciones vehiculares.</p>
                    <div class="dashboard__card-actions">
                        <a href="/checklist/panel" class="btn btn--dashboard">
                            <i class="fas fa-eye"></i> Ver Revisiones
                        </a>
                    </div>
                </article>

                <article class="dashboard__card">
                    <div class="dashboard__card-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h2 class="dashboard__card-title">Gestionar Aprendices</h2>
                    <p class="dashboard__card-description">Centraliza la administración de aprendices.</p>
                    <div class="dashboard__card-actions">
                        <a href="/usuarios/crear?tipo=aprendiz" class="btn btn--dashboard">
                            <i class="fas fa-plus"></i> Crear Aprendiz
                        </a>
                        <a href="/usuarios/aprendices" class="btn btn--dashboard">
                            <i class="fas fa-users"></i> Administrar Aprendices
                        </a>
                    </div>
                </article>

                <article class="dashboard__card">
                    <div class="dashboard__card-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h2 class="dashboard__card-title">Gestión de Instructores</h2>
                    <p class="dashboard__card-description">Administrar instructores del sistema.</p>
                    <div class="dashboard__card-actions">
                        <a href="/usuarios/crear?tipo=instructor" class="btn btn--dashboard">
                            <i class="fas fa-plus"></i> Crear Instructor
                        </a>
                        <a href="/usuarios/instructores" class="btn btn--dashboard">
                            <i class="fas fa-users"></i> Administrar Instructores
                        </a>
                    </div>
                </article>

                <article class="dashboard__card">
                    <div class="dashboard__card-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h2 class="dashboard__card-title">Checklist de Vehículos</h2>
                    <p class="dashboard__card-description">Gestión de inspecciones vehiculares.</p>
                    <div class="dashboard__card-actions">
                        <a href="/checklist" class="btn btn--dashboard">
                            <i class="fas fa-car"></i> Ir a Checklist
                        </a>
                    </div>
                </article>
            <?php elseif (($usuario['rol'] ?? '') === 'instructor'): ?>
                <article class="dashboard__card">
                    <div class="dashboard__card-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h2 class="dashboard__card-title">Checklist de Vehículos</h2>
                    <p class="dashboard__card-description">Gestión de inspecciones vehiculares.</p>
                    <div class="dashboard__card-actions">
                        <a href="/checklist" class="btn btn--dashboard">
                            <i class="fas fa-car"></i> Ir a Checklist
                        </a>
                    </div>
                </article>

                <article class="dashboard__card">
                    <div class="dashboard__card-icon">
                        <i class="fas fa-th-list"></i>
                    </div>
                    <h2 class="dashboard__card-title">Panel de Revisiones</h2>
                    <p class="dashboard__card-description">Supervisar inspecciones de aprendices.</p>
                    <div class="dashboard__card-actions">
                        <a href="/checklist/panel" class="btn btn--dashboard">
                            <i class="fas fa-eye"></i> Ver Revisiones
                        </a>
                    </div>
                </article>
            <?php else: ?>
                <article class="dashboard__card">
                    <div class="dashboard__card-icon">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <h2 class="dashboard__card-title">Módulo de Recepción</h2>
                    <p class="dashboard__card-description">Realizar recepción de vehículos.</p>
                    <div class="dashboard__card-actions">
                        <a href="/recepcion" class="btn btn--dashboard">
                            <i class="fas fa-car-side"></i> Entrar al Módulo
                        </a>
                    </div>
                </article>

                <article class="dashboard__card">
                    <div class="dashboard__card-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h2 class="dashboard__card-title">Checklist de Vehículos</h2>
                    <p class="dashboard__card-description">Acceso al checklist vehicular.</p>
                    <div class="dashboard__card-actions">
                        <a href="/checklist" class="btn btn--dashboard">
                            <i class="fas fa-car"></i> Ir a Checklist
                        </a>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
