<?php
$pre = $datosPrecargados ?? [];
$skipCabecera = !empty($skipPasoCabecera);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'MecaQuick') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <?php require ROOT_PATH . '/app/Views/partials/styles.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="/assets/js/checklist/checklist.js" defer></script>
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--form-page">
        <section class="panel panel--form">
            <h1 class="panel__title">
                <i class="fa-solid fa-clipboard-list panel__title-icon" aria-hidden="true"></i>
                Formato de checklist técnico – vehículos
            </h1>
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
                class="form form--with-icons checklist-form"
                data-total="<?= (int) ($totalPuntos ?? 0) ?>"
                data-save-url="/checklist/guardar-paso"
                <?= !empty($redirectAprendizAlFinalizar) ? ' data-redirect-aprendiz="1"' : '' ?>
                <?= $skipCabecera ? ' data-skip-cabecera="1"' : '' ?>
                novalidate
            >
                <input type="hidden" id="checklistToken" name="token" value="<?= htmlspecialchars($tokenInicial ?? '') ?>">

                <section class="checklist-step<?= $skipCabecera ? '' : ' is-active' ?>" data-step="0">
                    <div class="form__section">
                        <h3 class="form__section-title">
                            <i class="fa-solid fa-user form__section-icon" aria-hidden="true"></i>
                            1. Datos del cliente y vehículo
                        </h3>
                        <p class="checklist-step__hint">Todos los campos marcados con * son obligatorios.</p>
                        <div class="form__section-fields">
                            <div class="form__group form__group--full">
                                <label for="nombre_cliente">Nombre del cliente *</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" id="nombre_cliente" name="nombre_cliente" required placeholder="Ej. Juan Pérez" value="<?= htmlspecialchars($pre['nombre_cliente'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form__group">
                                <label for="cedula_nit">Cédula / NIT *</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-id-card"></i></span>
                                    <input type="text" id="cedula_nit" name="cedula_nit" required placeholder="Ej. 123456789" inputmode="numeric" pattern="[0-9]+" title="Solo números" value="<?= htmlspecialchars($pre['cedula_nit'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form__group">
                                <label for="telefono">Teléfono *</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                                    <input type="tel" id="telefono" name="telefono" required placeholder="Ej. 3001234567" maxlength="10" inputmode="numeric" title="10 dígitos iniciando por 3" value="<?= htmlspecialchars($pre['telefono'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form__group form__group--full">
                                <label for="correo">Correo *</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" id="correo" name="correo" required placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($pre['correo'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form__group form__group--full">
                                <label for="modelo_vehiculo">Modelo del vehículo *</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-car-side"></i></span>
                                    <input type="text" id="modelo_vehiculo" name="modelo_vehiculo" required placeholder="Ej. Chevrolet Spark 2020" value="<?= htmlspecialchars($pre['modelo_vehiculo'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form__group">
                                <label for="placa">Placa *</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-rectangle-list"></i></span>
                                    <input type="text" id="placa" name="placa" maxlength="6" required placeholder="Ej. ABC123" pattern="[A-Za-z]{3}[0-9]{3}" title="3 letras seguidas de 3 números" value="<?= htmlspecialchars($pre['placa'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form__group">
                                <label for="kilometraje">Kilometraje *</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-gauge-high"></i></span>
                                    <input type="number" id="kilometraje" name="kilometraje" min="0" required placeholder="Ej. 45000" value="<?= htmlspecialchars($pre['kilometraje'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form__group">
                                <label for="fecha_ingreso">Fecha de ingreso *</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-calendar"></i></span>
                                    <input type="date" id="fecha_ingreso" name="fecha_ingreso" required value="<?= htmlspecialchars($pre['fecha_ingreso'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form__group">
                                <label for="hora_ingreso">Hora *</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-clock"></i></span>
                                    <input type="time" id="hora_ingreso" name="hora_ingreso" required placeholder="Ej. 08:30" value="<?= htmlspecialchars($pre['hora_ingreso'] ?? '') ?>">
                                </div>
                            </div>
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
                            </div>
                            <div class="form__group checklist-question__observacion">
                                <label for="observaciones_punto_<?= $puntoId ?>">Observaciones</label>
                                <input type="text" id="observaciones_punto_<?= $puntoId ?>" name="observaciones_punto[<?= $puntoId ?>]" placeholder="Opcional">
                            </div>
                        </article>
                        <div class="form__actions">
                            <button type="button" class="btn btn--secondary" data-action="back">Anterior</button>
                            <button type="button" class="btn btn--primary" data-action="next">Siguiente</button>
                        </div>
                    </section>
                <?php endforeach; ?>

                <section class="checklist-step" data-step="final" hidden>
                    <div class="form__section">
                        <h3 class="form__section-title">
                            <i class="fa-solid fa-flag-checkered form__section-icon" aria-hidden="true"></i>
                            3. Observaciones generales y 4. Firmas
                        </h3>
                        <p class="checklist-step__hint">Complete los campos obligatorios antes de finalizar.</p>
                        <div class="form__section-fields">
                            <div class="form__group form__group--full">
                                <label for="observaciones_generales">Observaciones generales</label>
                                <div class="input-wrap input-wrap--textarea">
                                    <span class="input-wrap__icon input-wrap__icon--top" aria-hidden="true"><i class="fa-solid fa-comment-dots"></i></span>
                                    <textarea id="observaciones_generales" name="observaciones_generales" rows="4" placeholder="Observaciones adicionales del servicio..."></textarea>
                                </div>
                            </div>
                            <div class="form__group">
                                <label for="firma_tecnico">Firma del técnico</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-signature"></i></span>
                                    <input type="text" id="firma_tecnico" name="firma_tecnico" placeholder="Nombre o referencia">
                                </div>
                            </div>
                            <div class="form__group">
                                <label for="nombre_tecnico">Nombre del técnico *</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" id="nombre_tecnico" name="nombre_tecnico" required placeholder="Ej. Carlos Rodríguez" value="<?= htmlspecialchars($pre['nombre_tecnico'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="form__group">
                                <label for="firma_cliente">Firma del cliente</label>
                                <div class="input-wrap">
                                    <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-signature"></i></span>
                                    <input type="text" id="firma_cliente" name="firma_cliente" placeholder="Nombre o referencia">
                                </div>
                            </div>
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
