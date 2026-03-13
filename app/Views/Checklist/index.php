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
            <h1 class="panel__title">Checklist técnico – vehículos</h1>
            <p class="panel__intro">
                <?php if ($skipCabecera): ?>
                    Los datos del cliente y vehículo ya están registrados. Complete los 25 puntos de revisión; cada avance se guarda al pulsar "Siguiente".
                <?php else: ?>
                    Complete los datos del cliente y vehículo, luego los 25 puntos de revisión. Cada avance se guarda al pulsar "Siguiente".
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
                    <h2 class="checklist-step__title">1. Datos del Cliente y Vehículo</h2>
                    <p class="checklist-step__hint">Todos los campos marcados con * son obligatorios.</p>
                    <div class="checklist-grid checklist-grid--cabecera">
                        <div class="form__group">
                            <label for="cliente_nombre">Nombre del Cliente *</label>
                            <input type="text" id="cliente_nombre" name="cliente_nombre" required placeholder="Nombre completo" value="<?= htmlspecialchars($c['cliente_nombre'] ?? $c['asesor'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="cliente_documento">Cédula / NIT *</label>
                            <input type="text" id="cliente_documento" name="cliente_documento" required placeholder="Identificación" value="<?= htmlspecialchars($c['cliente_documento'] ?? $c['tipo_comercial_codigo'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="cliente_telefono">Teléfono *</label>
                            <input type="text" id="cliente_telefono" name="cliente_telefono" required placeholder="Teléfono" value="<?= htmlspecialchars($c['cliente_telefono'] ?? $c['ldc'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="cliente_email">Correo *</label>
                            <input type="email" id="cliente_email" name="cliente_email" required placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($c['cliente_email'] ?? $c['vhn'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="tipo_comercial_modelo">Modelo del Vehículo *</label>
                            <input type="text" id="tipo_comercial_modelo" name="tipo_comercial_modelo" required placeholder="Modelo" value="<?= htmlspecialchars($c['modelo'] ?? $c['tipo_comercial_modelo'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="matricula">Placa *</label>
                            <input type="text" id="matricula" name="matricula" required placeholder="Ej. ABC123" value="<?= htmlspecialchars($c['placa'] ?? $c['matricula'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="kilometraje">Kilometraje *</label>
                            <input type="number" id="kilometraje" name="kilometraje" min="0" required placeholder="KM actuales" value="<?= htmlspecialchars((string) ($c['kilometraje'] ?? '')) ?>">
                        </div>
                        <div class="form__group">
                            <label for="fecha_ingreso">Fecha de Ingreso *</label>
                            <input type="date" id="fecha_ingreso" name="fecha_ingreso" required value="<?= htmlspecialchars($c['fecha_ingreso'] ?? $c['fecha_servicio'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="hora_ingreso">Hora *</label>
                            <input type="time" id="hora_ingreso" name="hora_ingreso" required value="<?= htmlspecialchars($c['hora_ingreso'] ?? $c['djka'] ?? date('H:i')) ?>">
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
                        <h2 class="checklist-step__title">2. Checklist de 25 Puntos — Punto <?= $numeroPunto ?> de <?= (int) ($totalPuntos ?? 0) ?></h2>
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
                                    <input type="radio" name="responses[<?= $puntoId ?>]" value="bueno" required>
                                    Bueno
                                </label>
                                <label>
                                    <input type="radio" name="responses[<?= $puntoId ?>]" value="regular">
                                    Regular
                                </label>
                                <label>
                                    <input type="radio" name="responses[<?= $puntoId ?>]" value="malo">
                                    Malo
                                </label>
                                <label>
                                    <input type="radio" name="responses[<?= $puntoId ?>]" value="no_aplica">
                                    No aplica
                                </label>
                            </div>
                            <div class="form__group checklist-question__observacion">
                                <label for="observacion_<?= $puntoId ?>">Observaciones</label>
                                <input type="text" id="observacion_<?= $puntoId ?>" name="observaciones_puntos[<?= $puntoId ?>]" placeholder="Observación específica de este punto (opcional)">
                            </div>
                        </article>
                        <div class="form__actions">
                            <button type="button" class="btn btn--secondary" data-action="back">Anterior</button>
                            <button type="button" class="btn btn--primary" data-action="next">Siguiente</button>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="checklist-step" data-step="final">
                    <h2 class="checklist-step__title">3. Observaciones Generales</h2>
                    <p class="checklist-step__hint">Anote observaciones generales del mantenimiento (no por punto).</p>
                    <div class="checklist-grid">
                        <div class="form__group form__group--full">
                            <label for="observaciones_generales">Observaciones generales del mantenimiento</label>
                            <textarea id="observaciones_generales" name="observaciones_generales" rows="4" placeholder="Observaciones generales sobre la revisión (ej. ninguna, cambio de filtros pendiente...)"><?= htmlspecialchars($c['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <h2 class="checklist-step__title" style="margin-top: 1.25rem;">4. Firmas</h2>
                    <div class="checklist-grid checklist-grid--cabecera">
                        <div class="form__group">
                            <label for="firma_tecnico">Firma del Técnico *</label>
                            <input type="text" id="firma_tecnico" name="firma_tecnico" required placeholder="Firma del técnico" value="<?= htmlspecialchars($c['firma_tecnico'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="nombre_tecnico">Nombre del Técnico *</label>
                            <input type="text" id="nombre_tecnico" name="nombre_tecnico" required placeholder="Nombre completo" value="<?= htmlspecialchars($c['nombre_tecnico'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="firma_cliente">Firma del Cliente *</label>
                            <input type="text" id="firma_cliente" name="firma_cliente" required placeholder="Firma del cliente" value="<?= htmlspecialchars($c['firma_cliente'] ?? '') ?>">
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
