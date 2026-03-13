<?php
$errores = $_SESSION['mantenimiento_crear_errores'] ?? [];
$datos = $_SESSION['mantenimiento_crear_datos'] ?? [];
$asesorVal = $datos['asesor'] ?? $nombreTutor ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Crear mantenimiento') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--form-page">
        <section class="panel panel--form">
            <h1 class="panel__title">Crear nuevo mantenimiento</h1>
            <p class="panel__intro">
                Complete los datos del vehículo y de la orden. El tutor del módulo será asignado automáticamente. Tras guardar, pasará directamente a completar los puntos del checklist de revisión.
            </p>

            <div class="dashboard__actions" style="margin-bottom: 1rem;">
                <a href="/mantenimiento" class="btn btn--secondary">Volver al módulo</a>
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

            <form action="/mantenimiento/guardar-nuevo" method="post" class="mantenimiento-crear-form">
                <div class="checklist-grid checklist-grid--cabecera">
                    <div class="form__group">
                        <label for="numero_orden">Núm. de orden *</label>
                        <input type="text" id="numero_orden" name="numero_orden" required placeholder="Ej. GAGBCW" value="<?= htmlspecialchars($datos['numero_orden'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="tipo_comercial_codigo">Tipo comercial (código) *</label>
                        <input type="text" id="tipo_comercial_codigo" name="tipo_comercial_codigo" required placeholder="Ej. GAGBCW" value="<?= htmlspecialchars($datos['tipo_comercial_codigo'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="matricula">Matrícula *</label>
                        <input type="text" id="matricula" name="matricula" required placeholder="Ej. NLZ988" value="<?= htmlspecialchars($datos['matricula'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="matriculacion">Matriculación *</label>
                        <input type="date" id="matriculacion" name="matriculacion" required value="<?= htmlspecialchars($datos['matriculacion'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="bastidor">Número de bastidor *</label>
                        <input type="text" id="bastidor" name="bastidor" required placeholder="Ej. WAUZZZGA9PA022533" value="<?= htmlspecialchars($datos['bastidor'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="ldm">LDM</label>
                        <input type="text" id="ldm" name="ldm" placeholder="Opcional" value="<?= htmlspecialchars($datos['ldm'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="djka">DJKA</label>
                        <input type="text" id="djka" name="djka" placeholder="Opcional" value="<?= htmlspecialchars($datos['djka'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="kilometraje">Kilometraje *</label>
                        <input type="number" id="kilometraje" name="kilometraje" min="0" required placeholder="Ej. 44454" value="<?= htmlspecialchars((string) ($datos['kilometraje'] ?? '')) ?>">
                    </div>
                    <div class="form__group">
                        <label for="asesor">Asesor del servicio *</label>
                        <input type="text" id="asesor" name="asesor" required placeholder="Ej. nombre del tutor" value="<?= htmlspecialchars($asesorVal) ?>">
                    </div>
                    <div class="form__group">
                        <label for="tipo_comercial_modelo">Tipo comercial (modelo) *</label>
                        <input type="text" id="tipo_comercial_modelo" name="tipo_comercial_modelo" required placeholder="Ej. Q2 1,4 L4110 A8" value="<?= htmlspecialchars($datos['tipo_comercial_modelo'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="ldc">LDC</label>
                        <input type="text" id="ldc" name="ldc" placeholder="Opcional" value="<?= htmlspecialchars($datos['ldc'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="vhn">VHN</label>
                        <input type="text" id="vhn" name="vhn" placeholder="Opcional" value="<?= htmlspecialchars($datos['vhn'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="ano_modelo">Año de modelos *</label>
                        <input type="number" id="ano_modelo" name="ano_modelo" min="1950" max="2030" required placeholder="Ej. 2023" value="<?= htmlspecialchars($datos['ano_modelo'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="fecha_servicio">Fecha de servicio *</label>
                        <input type="date" id="fecha_servicio" name="fecha_servicio" required value="<?= htmlspecialchars($datos['fecha_servicio'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="tipo_inspeccion">Tipo de inspección *</label>
                        <input type="text" id="tipo_inspeccion" name="tipo_inspeccion" required placeholder="Ej. Inspección con cambio de aceite" value="<?= htmlspecialchars($datos['tipo_inspeccion'] ?? '') ?>">
                    </div>
                    <div class="form__group form__group--full">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="2" placeholder="Opcional"><?= htmlspecialchars($datos['observaciones'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="form__actions" style="margin-top: 1.25rem;">
                    <a href="/mantenimiento" class="btn btn--secondary">Cancelar</a>
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
