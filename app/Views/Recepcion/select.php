<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Recepción') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <?php require ROOT_PATH . '/app/Views/partials/styles.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--form-page">
        <section class="panel panel--form">
            <h1 class="panel__title">
                <i class="fa-solid fa-car-side panel__title-icon" aria-hidden="true"></i>
                Recepción del vehículo
            </h1>
            <p class="panel__intro">
                Seleccione la cita del día para iniciar el proceso de recepción. Al finalizar se creará la inspección y podrá continuar con el checklist técnico.
            </p>

            <?php if (!empty($errorDb ?? null)): ?>
                <div class="alert alert--error" role="alert">
                    <strong>Error:</strong> <?= htmlspecialchars((string) ($errorDb ?? '')) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($citas ?? [])): ?>
                <div class="alert alert--info" role="status">
                    <i class="fa-solid fa-circle-info alert__icon" aria-hidden="true"></i>
                    No hay citas asignadas para hoy. Las citas del día aparecerán aquí cuando existan registros en <code>fechas_disponibles</code> con fecha igual a hoy.
                </div>
            <?php else: ?>
                <ul class="recepcion-list">
                    <?php foreach ($citas as $cita): ?>
                        <li class="recepcion-list__item">
                            <a href="/recepcion/form/<?= (int) $cita['cita_id'] ?>" class="recepcion-list__link">
                                <span class="recepcion-list__placa"><?= htmlspecialchars($cita['placa'] ?? '') ?></span>
                                <span class="recepcion-list__cliente"><?= htmlspecialchars(trim(($cita['nombre'] ?? '') . ' ' . ($cita['apellido'] ?? ''))) ?></span>
                                <span class="recepcion-list__vehiculo"><?= htmlspecialchars(trim(($cita['marca'] ?? '') . ' ' . ($cita['modelo'] ?? '') . ' ' . ($cita['anio'] ?? ''))) ?></span>
                                <i class="fa-solid fa-chevron-right recepcion-list__icon" aria-hidden="true"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
