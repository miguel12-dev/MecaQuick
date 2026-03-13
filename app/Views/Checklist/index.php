<?php
$c = $cabeceraPrecargada ?? [];
$skipCabecera = !empty($skipPasoCabecera);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'MecaQuick') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="/assets/js/checklist/checklist.js" defer></script>
</head>
<body class="page">
    <?php require dirname(__DIR__, 2) . '/Views/partials/header.php'; ?>

    <main class="layout layout--form-page">
        <section class="panel panel--form">
            <h1 class="panel__title">Checklist de mantenimiento</h1>
            <p class="panel__intro">
                <?php if ($skipCabecera): ?>
                    Los datos del vehículo ya están registrados. Complete los puntos de inspección; cada avance se guarda al pulsar "Siguiente".
                <?php else: ?>
                    Formulario de acceso libre para pruebas. Complete todos los campos obligatorios antes de continuar. Cada avance se guarda al pulsar "Siguiente".
                <?php endif; ?>
            </p>

            <div class="checklist-progress" aria-live="polite">
                <div class="checklist-progress__meta">
                    <span>Avance</span>
                    <span id="checklistProgressLabel">0/<?= (int) ($totalPuntos ?? 0) ?> (0%)</span>
                </div>
                <div class="checklist-progress__track">
                    <div id="checklistProgressBar" class="checklist-progress__bar"></div>
                </div>
            </div>

            <?php if (!empty($errorDb ?? null)): ?>
                <div class="alert alert--error" role="alert">
                    <strong>Error de configuración:</strong> <?= htmlspecialchars((string) ($errorDb ?? '')) ?>
                </div>
            <?php endif; ?>
            <div id="checklistMessage" class="alert alert--success checklist-message checklist-message--hidden" role="status"></div>

            <form
                id="checklistForm"
                class="checklist-form"
                data-total="<?= (int) ($totalPuntos ?? 0) ?>"
                data-save-url="/checklist/guardar-paso"
                <?= !empty($redirectAprendizAlFinalizar) ? ' data-redirect-aprendiz="1"' : '' ?>
                <?= $skipCabecera ? ' data-skip-cabecera="1"' : '' ?>
                novalidate
            >
                <input type="hidden" id="checklistToken" name="token" value="<?= htmlspecialchars($tokenInicial ?? '') ?>">

                <section class="checklist-step<?= $skipCabecera ? '' : ' is-active' ?>" data-step="0">
                    <h2 class="checklist-step__title">Datos generales</h2>
                    <p class="checklist-step__hint">Todos los campos marcados con * son obligatorios.</p>
                    <div class="checklist-grid checklist-grid--cabecera">
                        <div class="form__group">
                            <label for="numero_orden">Núm. de orden *</label>
                            <input type="text" id="numero_orden" name="numero_orden" required placeholder="Ej. GAGBCW" value="<?= htmlspecialchars($c['numero_orden'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="tipo_comercial_codigo">Tipo comercial (código) *</label>
                            <input type="text" id="tipo_comercial_codigo" name="tipo_comercial_codigo" required placeholder="Ej. GAGBCW" value="<?= htmlspecialchars($c['tipo_comercial_codigo'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="matricula">Matrícula *</label>
                            <input type="text" id="matricula" name="matricula" required placeholder="Ej. NLZ988" value="<?= htmlspecialchars($c['matricula'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="matriculacion">Matriculación *</label>
                            <input type="date" id="matriculacion" name="matriculacion" required value="<?= htmlspecialchars($c['matriculacion'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="bastidor">Número de bastidor *</label>
                            <input type="text" id="bastidor" name="bastidor" required placeholder="Ej. WAUZZZGA9PA022533" value="<?= htmlspecialchars($c['bastidor'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="ldm">LDM</label>
                            <input type="text" id="ldm" name="ldm" placeholder="Opcional" value="<?= htmlspecialchars($c['ldm'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="djka">DJKA</label>
                            <input type="text" id="djka" name="djka" placeholder="Opcional" value="<?= htmlspecialchars($c['djka'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="kilometraje">Kilometraje *</label>
                            <input type="number" id="kilometraje" name="kilometraje" min="0" required placeholder="Ej. 44454" value="<?= htmlspecialchars((string) ($c['kilometraje'] ?? '')) ?>">
                        </div>
                        <div class="form__group">
                            <label for="asesor">Asesor del servicio *</label>
                            <input type="text" id="asesor" name="asesor" required placeholder="Ej. Vásquez, Luz Yamile" value="<?= htmlspecialchars($c['asesor'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="tipo_comercial_modelo">Tipo comercial (modelo) *</label>
                            <input type="text" id="tipo_comercial_modelo" name="tipo_comercial_modelo" required placeholder="Ej. Q2 1,4 L4110 A8" value="<?= htmlspecialchars($c['tipo_comercial_modelo'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="ldc">LDC</label>
                            <input type="text" id="ldc" name="ldc" placeholder="Opcional" value="<?= htmlspecialchars($c['ldc'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="vhn">VHN</label>
                            <input type="text" id="vhn" name="vhn" placeholder="Opcional" value="<?= htmlspecialchars($c['vhn'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="ano_modelo">Año de modelos *</label>
                            <input type="number" id="ano_modelo" name="ano_modelo" min="1950" max="2030" required placeholder="Ej. 2023" value="<?= htmlspecialchars($c['ano_modelo'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="fecha_servicio">Fecha de servicio *</label>
                            <input type="date" id="fecha_servicio" name="fecha_servicio" required value="<?= htmlspecialchars($c['fecha_servicio'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="tipo_inspeccion">Tipo de inspección *</label>
                            <input type="text" id="tipo_inspeccion" name="tipo_inspeccion" required placeholder="Ej. Inspección con cambio de aceite" value="<?= htmlspecialchars($c['tipo_inspeccion'] ?? '') ?>">
                        </div>
                        <div class="form__group form__group--full">
                            <label for="observaciones">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="2" placeholder="Opcional"><?= htmlspecialchars($c['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="form__actions">
                        <button type="button" class="btn btn--primary" data-action="next">Siguiente</button>
                    </div>
                </section>

                <?php foreach (($puntos ?? []) as $indice => $punto): ?>
                    <?php
                    $puntoId = (int) $punto['id'];
                    $numeroPunto = (int) $punto['numero_punto'];
                    $descripcion = (string) $punto['descripcion'];
                    $unidadMedida = (string) ($punto['unidad_medida'] ?? 'N/A');
                    $requiereValorMedido = $unidadMedida !== 'N/A';
                    $esPrimerPunto = $indice === 0;
                    ?>
                    <section class="checklist-step<?= $skipCabecera && $esPrimerPunto ? ' is-active' : '' ?>" data-step="<?= $puntoId ?>" data-punto-id="<?= $puntoId ?>">
                        <h2 class="checklist-step__title">Punto <?= $numeroPunto ?> de <?= (int) ($totalPuntos ?? 0) ?></h2>
                        <article class="checklist-question">
                            <p class="checklist-question__text"><?= htmlspecialchars($descripcion) ?></p>
                            <?php if ($requiereValorMedido): ?>
                                <div class="form__group checklist-question__valor">
                                    <label for="valor_medido_<?= $puntoId ?>">Valor (<?= htmlspecialchars($unidadMedida) ?>)</label>
                                    <input type="text" id="valor_medido_<?= $puntoId ?>" name="valor_medido[<?= $puntoId ?>]" placeholder="Ej. 4, 09/2025">
                                </div>
                            <?php endif; ?>
                            <div class="checklist-question__options">
                                <label>
                                    <input type="radio" name="responses[<?= $puntoId ?>]" value="si" required>
                                    OK/realizado
                                </label>
                                <label>
                                    <input type="radio" name="responses[<?= $puntoId ?>]" value="no">
                                    No OK
                                </label>
                                <label>
                                    <input type="radio" name="responses[<?= $puntoId ?>]" value="subsanado">
                                    Subsanada
                                </label>
                            </div>
                        </article>
                        <div class="form__actions">
                            <button type="button" class="btn btn--secondary" data-action="back">Anterior</button>
                            <button type="button" class="btn btn--primary" data-action="next">Siguiente</button>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="checklist-step" data-step="final">
                    <h2 class="checklist-step__title">Datos finales</h2>
                    <p class="checklist-step__hint">Complete los campos obligatorios antes de finalizar.</p>
                    <div class="checklist-grid">
                        <div class="form__group">
                            <label for="km_salida">Salida (km) *</label>
                            <input type="number" id="km_salida" name="km_salida" min="0" required placeholder="Ej. 44454">
                        </div>
                        <div class="form__group">
                            <label for="km_llegada">Llegada (km) *</label>
                            <input type="number" id="km_llegada" name="km_llegada" min="0" required placeholder="Ej. 44463">
                        </div>
                        <div class="form__group form__group--full">
                            <label for="nota_mantenimiento">Nota de mantenimiento *</label>
                            <textarea id="nota_mantenimiento" name="nota_mantenimiento" rows="4" required placeholder="Describa los trabajos realizados..."></textarea>
                        </div>
                        <div class="form__group">
                            <label for="fecha_firma_responsable">Fecha/firma (responsable) *</label>
                            <input type="date" id="fecha_firma_responsable" name="fecha_firma_responsable" required>
                        </div>
                        <div class="form__group">
                            <label for="fecha_firma_control">Fecha/firma (control final) *</label>
                            <input type="date" id="fecha_firma_control" name="fecha_firma_control" required>
                        </div>
                    </div>
                    <div class="form__actions">
                        <button type="button" class="btn btn--secondary" data-action="back">Anterior</button>
                        <button type="button" class="btn btn--primary" data-action="finish">Aceptar y finalizar</button>
                    </div>
                </section>
            </form>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Registro de checklist de revisión mecánica</span>
    </footer>
</body>
</html>
