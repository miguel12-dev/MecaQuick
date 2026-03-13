<?php
$errores = $_SESSION['mantenimiento_crear_errores'] ?? [];
$datos = $datos ?? [];
$citaId = $cita_id ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Crear recepción') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--form-page">
        <section class="panel panel--form">
            <h1 class="panel__title">Crear nueva recepción</h1>
            <p class="panel__intro">
                Complete los datos del vehículo y de la orden. El tutor del módulo será asignado automáticamente. Tras guardar, pasará directamente a completar los puntos del checklist de revisión.
            </p>

            <div class="dashboard__actions" style="margin-bottom: 1rem;">
                <a href="/recepcion/crear" class="btn btn--secondary">Volver a citas del día</a>
                <a href="/recepcion" class="btn btn--secondary">Volver al módulo</a>
            </div>

            <?php if ($errores !== []): ?>
                <div class="mantenimiento-form__errors" role="alert">
                    <ul>
                        <?php foreach ($errores as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/recepcion/guardar-nuevo" method="post" class="mantenimiento-crear-form">
                <input type="hidden" name="cita_id" value="<?= htmlspecialchars((string) ($citaId ?? 'sin-cita')) ?>">

                <h2 class="panel__subtitle">1. Datos del Cliente y Vehículo</h2>
                <div class="checklist-grid checklist-grid--cabecera">
                    <div class="form__group">
                        <label for="cliente_nombre">Nombre del Cliente *</label>
                        <input type="text" id="cliente_nombre" name="cliente_nombre" required placeholder="Nombre completo" value="<?= htmlspecialchars($datos['cliente_nombre'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_documento">Cédula / NIT *</label>
                        <input type="text" id="cliente_documento" name="cliente_documento" required placeholder="Identificación" value="<?= htmlspecialchars($datos['cliente_documento'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_telefono">Teléfono *</label>
                        <input type="text" id="cliente_telefono" name="cliente_telefono" required placeholder="Teléfono" value="<?= htmlspecialchars($datos['cliente_telefono'] ?? $datos['cliente_celular'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_email">Correo *</label>
                        <input type="email" id="cliente_email" name="cliente_email" required placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($datos['cliente_email'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="tipo_comercial_modelo">Modelo del Vehículo *</label>
                        <input type="text" id="tipo_comercial_modelo" name="tipo_comercial_modelo" required placeholder="Modelo" value="<?= htmlspecialchars($datos['tipo_comercial_modelo'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="matricula">Placa *</label>
                        <input type="text" id="matricula" name="matricula" required placeholder="Ej. ABC123" value="<?= htmlspecialchars($datos['matricula'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="kilometraje">Kilometraje *</label>
                        <input type="number" id="kilometraje" name="kilometraje" min="0" required placeholder="KM actuales" value="<?= htmlspecialchars((string) ($datos['kilometraje'] ?? '')) ?>">
                    </div>
                    <div class="form__group">
                        <label for="fecha_ingreso">Fecha de Ingreso *</label>
                        <input type="date" id="fecha_ingreso" name="fecha_ingreso" required value="<?= htmlspecialchars($datos['fecha_ingreso'] ?? $datos['fecha_servicio'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="form__group">
                        <label for="hora_ingreso">Hora *</label>
                        <input type="time" id="hora_ingreso" name="hora_ingreso" required value="<?= htmlspecialchars($datos['hora_ingreso'] ?? date('H:i')) ?>">
                    </div>
                </div>
                <div class="form__actions" style="margin-top: 1.25rem;">
                    <a href="/recepcion" class="btn btn--secondary">Cancelar</a>
                    <button type="submit" class="btn btn--primary">Crear y continuar al checklist</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
