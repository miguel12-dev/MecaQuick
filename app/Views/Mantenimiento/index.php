<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Módulo de mantenimiento') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <div class="dashboard__header">
            <div class="dashboard__header-icon">
                <i class="fas fa-tools"></i>
            </div>
            <h1 class="dashboard__header-title">Módulo de Mantenimiento</h1>
            <p class="dashboard__header-subtitle">Gestiona tu aprendizaje y realiza inspecciones vehiculares.</p>
        </div>

        <div class="dashboard__grid">
            <article class="dashboard__card">
                <div class="dashboard__card-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h2 class="dashboard__card-title">Aprendizaje</h2>
                <p class="dashboard__card-description">Accede al contenido formativo del módulo.</p>
                <div class="dashboard__card-actions">
                    <a href="/mantenimiento/aprendizaje" class="btn btn--dashboard">
                        <i class="fas fa-graduation-cap"></i> Ir a Aprendizaje
                    </a>
                </div>
            </article>

            <article class="dashboard__card">
                <div class="dashboard__card-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h2 class="dashboard__card-title">Mis Revisiones</h2>
                <p class="dashboard__card-description">Consulta los vehículos y checklists realizados.</p>
                <div class="dashboard__card-actions">
                    <a href="/mantenimiento/mis-revisiones" class="btn btn--dashboard">
                        <i class="fas fa-list-alt"></i> Ver Revisiones
                    </a>
                </div>
            </article>

            <article class="dashboard__card">
                <div class="dashboard__card-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h2 class="dashboard__card-title">Nueva Inspección</h2>
                <p class="dashboard__card-description">Crear checklist o mantenimiento vehicular.</p>
                <div class="dashboard__card-actions">
                    <a href="/mantenimiento/crear" class="btn btn--dashboard">
                        <i class="fas fa-car"></i> Crear Inspección
                    </a>
                </div>
            </article>
        </div>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
