<?php
$c = $cita ?? [];
$r = $recepcion ?? [];
$accInt = $accesoriosInternos ?? [];
$accExt = $accesoriosExternos ?? [];
$nombreCompleto = trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
$modeloVehiculo = trim(($c['marca'] ?? '') . ' ' . ($c['modelo'] ?? '') . ' ' . ($c['anio'] ?? ''));
$accesoriosIntVal = is_array($r['accesorios_internos'] ?? null) ? $r['accesorios_internos'] : (json_decode((string) ($r['accesorios_internos'] ?? '{}'), true) ?: []);
$accesoriosExtVal = is_array($r['accesorios_externos'] ?? null) ? $r['accesorios_externos'] : (json_decode((string) ($r['accesorios_externos'] ?? '{}'), true) ?: []);
?>
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
                <i class="fa-solid fa-clipboard-check panel__title-icon" aria-hidden="true"></i>
                Formulario de recepción
            </h1>
            <p class="panel__intro">
                Complete los datos de recepción del vehículo. Los datos del cliente y vehículo están precargados desde la cita.
            </p>

            <form action="/recepcion/guardar" method="post" class="form form--with-icons">
                <input type="hidden" name="cita_id" value="<?= (int) ($c['cita_id'] ?? 0) ?>">

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-user form__section-icon" aria-hidden="true"></i>
                        Cliente y vehículo (precargados)
                    </h3>
                    <div class="form__section-fields form__section-fields--readonly">
                        <div class="form__group">
                            <label>Cliente</label>
                            <p class="form__readonly"><?= htmlspecialchars($nombreCompleto) ?></p>
                        </div>
                        <div class="form__group">
                            <label>Documento</label>
                            <p class="form__readonly"><?= htmlspecialchars($c['documento'] ?? '') ?></p>
                        </div>
                        <div class="form__group">
                            <label>Teléfono</label>
                            <p class="form__readonly"><?= htmlspecialchars($c['telefono'] ?? '') ?></p>
                        </div>
                        <div class="form__group">
                            <label>Correo</label>
                            <p class="form__readonly"><?= htmlspecialchars($c['email'] ?? '') ?></p>
                        </div>
                        <div class="form__group">
                            <label>Vehículo</label>
                            <p class="form__readonly"><?= htmlspecialchars($modeloVehiculo) ?></p>
                        </div>
                        <div class="form__group">
                            <label>Placa</label>
                            <p class="form__readonly"><?= htmlspecialchars($c['placa'] ?? '') ?></p>
                        </div>
                    </div>
                </div>

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-location-dot form__section-icon" aria-hidden="true"></i>
                        Dirección del cliente
                    </h3>
                    <div class="form__section-fields">
                        <div class="form__group form__group--full">
                            <label for="direccion">Dirección</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-map-marker-alt"></i></span>
                                <input type="text" id="direccion" name="direccion" placeholder="Ej. Calle 10 # 5-20"
                                       value="<?= htmlspecialchars($c['direccion'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="ciudad">Ciudad</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-city"></i></span>
                                <input type="text" id="ciudad" name="ciudad" placeholder="Ej. Florencia"
                                       value="<?= htmlspecialchars($c['ciudad'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-car form__section-icon" aria-hidden="true"></i>
                        Datos del vehículo en recepción
                    </h3>
                    <div class="form__section-fields">
                        <div class="form__group">
                            <label for="kilometraje_recepcion">Kilometraje *</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-gauge-high"></i></span>
                                <input type="number" id="kilometraje_recepcion" name="kilometraje_recepcion" min="0" required
                                       placeholder="Ej. 45000" value="<?= htmlspecialchars($r['kilometraje_recepcion'] ?? $c['kilometraje'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="vin">VIN</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-barcode"></i></span>
                                <input type="text" id="vin" name="vin" placeholder="Número VIN"
                                       value="<?= htmlspecialchars($c['vin'] ?? $r['vin'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="numero_motor">Número de motor</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-gears"></i></span>
                                <input type="text" id="numero_motor" name="numero_motor" placeholder="Número de motor"
                                       value="<?= htmlspecialchars($c['numero_motor'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="fecha_venta">Fecha de venta</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-calendar"></i></span>
                                <input type="date" id="fecha_venta" name="fecha_venta"
                                       value="<?= htmlspecialchars($c['fecha_venta'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-wrench form__section-icon" aria-hidden="true"></i>
                        Servicio anterior
                    </h3>
                    <div class="form__section-fields">
                        <div class="form__group">
                            <label for="fecha_servicio_anterior">Fecha</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-calendar"></i></span>
                                <input type="date" id="fecha_servicio_anterior" name="fecha_servicio_anterior"
                                       value="<?= htmlspecialchars($r['fecha_servicio_anterior'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="or_numero">OR Nº</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-hashtag"></i></span>
                                <input type="text" id="or_numero" name="or_numero" placeholder="Número de orden"
                                       value="<?= htmlspecialchars($r['or_numero'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form__group form__group--full">
                            <label for="tipo_servicio_anterior">Tipo de servicio</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                                <input type="text" id="tipo_servicio_anterior" name="tipo_servicio_anterior"
                                       placeholder="Ej. Cambio de aceite" value="<?= htmlspecialchars($r['tipo_servicio_anterior'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="km_servicio_anterior">Km servicio anterior</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-gauge-high"></i></span>
                                <input type="number" id="km_servicio_anterior" name="km_servicio_anterior" min="0"
                                       value="<?= htmlspecialchars($r['km_servicio_anterior'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-user-driver form__section-icon" aria-hidden="true"></i>
                        Vehículo conducido por
                    </h3>
                    <div class="form__section-fields">
                        <div class="form__group">
                            <select id="vehiculo_conducido_por" name="vehiculo_conducido_por">
                                <option value="dueno" <?= ($r['vehiculo_conducido_por'] ?? 'dueno') === 'dueno' ? 'selected' : '' ?>>Dueño</option>
                                <option value="chofer" <?= ($r['vehiculo_conducido_por'] ?? '') === 'chofer' ? 'selected' : '' ?>>Chofer</option>
                                <option value="familiar" <?= ($r['vehiculo_conducido_por'] ?? '') === 'familiar' ? 'selected' : '' ?>>Familiar</option>
                                <option value="otro" <?= ($r['vehiculo_conducido_por'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-boxes-stacked form__section-icon" aria-hidden="true"></i>
                        Accesorios internos
                    </h3>
                    <div class="form__section-fields form__section-fields--grid-2">
                        <?php foreach ($accInt as $key => $label): ?>
                            <div class="form__group form__group--checkbox">
                                <label>
                                    <input type="radio" name="accesorio_int_<?= htmlspecialchars($key) ?>" value="si"
                                           <?= ($accesoriosIntVal[$key] ?? 'no') === 'si' ? 'checked' : '' ?>>
                                    Sí
                                </label>
                                <label>
                                    <input type="radio" name="accesorio_int_<?= htmlspecialchars($key) ?>" value="no"
                                           <?= ($accesoriosIntVal[$key] ?? 'no') !== 'si' ? 'checked' : '' ?>>
                                    No
                                </label>
                                <span class="form__checkbox-label"><?= htmlspecialchars($label) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-car-side form__section-icon" aria-hidden="true"></i>
                        Accesorios externos
                    </h3>
                    <div class="form__section-fields form__section-fields--grid-2">
                        <?php foreach ($accExt as $key => $label): ?>
                            <div class="form__group form__group--checkbox">
                                <label>
                                    <input type="radio" name="accesorio_ext_<?= htmlspecialchars($key) ?>" value="si"
                                           <?= ($accesoriosExtVal[$key] ?? 'no') === 'si' ? 'checked' : '' ?>>
                                    Sí
                                </label>
                                <label>
                                    <input type="radio" name="accesorio_ext_<?= htmlspecialchars($key) ?>" value="no"
                                           <?= ($accesoriosExtVal[$key] ?? 'no') !== 'si' ? 'checked' : '' ?>>
                                    No
                                </label>
                                <span class="form__checkbox-label"><?= htmlspecialchars($label) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-coins form__section-icon" aria-hidden="true"></i>
                        Presupuesto y método de pago
                    </h3>
                    <div class="form__section-fields">
                        <div class="form__group">
                            <label for="presupuesto_repuestos">Repuestos</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-dollar-sign"></i></span>
                                <input type="number" id="presupuesto_repuestos" name="presupuesto_repuestos" min="0" step="0.01"
                                       value="<?= htmlspecialchars($r['presupuesto_repuestos'] ?? '0') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="presupuesto_mano_obra">Mano de obra</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-dollar-sign"></i></span>
                                <input type="number" id="presupuesto_mano_obra" name="presupuesto_mano_obra" min="0" step="0.01"
                                       value="<?= htmlspecialchars($r['presupuesto_mano_obra'] ?? '0') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="presupuesto_total">Total</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-dollar-sign"></i></span>
                                <input type="number" id="presupuesto_total" name="presupuesto_total" min="0" step="0.01"
                                       value="<?= htmlspecialchars($r['presupuesto_total'] ?? '0') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="metodo_pago">Método de pago</label>
                            <select id="metodo_pago" name="metodo_pago">
                                <option value="efectivo" <?= ($r['metodo_pago'] ?? 'efectivo') === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                                <option value="tarjeta_credito" <?= ($r['metodo_pago'] ?? '') === 'tarjeta_credito' ? 'selected' : '' ?>>Tarjeta de crédito</option>
                                <option value="otro" <?= ($r['metodo_pago'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>
                        <div class="form__group form__group--full">
                            <label class="form__checkbox-wrap">
                                <input type="checkbox" name="recibo_repuesto_cambiados" value="1"
                                       <?= !empty($r['recibo_repuesto_cambiados']) ? 'checked' : '' ?>>
                                Recibo de repuestos cambiados
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-note-sticky form__section-icon" aria-hidden="true"></i>
                        Observaciones y defectos
                    </h3>
                    <div class="form__section-fields">
                        <div class="form__group form__group--full">
                            <label for="observaciones">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="3"
                                      placeholder="Observaciones generales"><?= htmlspecialchars($r['observaciones'] ?? '') ?></textarea>
                        </div>
                        <div class="form__group form__group--full">
                            <label for="defectos_carroceria">Defectos de carrocería</label>
                            <textarea id="defectos_carroceria" name="defectos_carroceria" rows="2"
                                      placeholder="Rasguños, abolladuras, etc."><?= htmlspecialchars($r['defectos_carroceria'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-signature form__section-icon" aria-hidden="true"></i>
                        Inventariado por y firma del cliente
                    </h3>
                    <div class="form__section-fields">
                        <div class="form__group">
                            <label for="inventariado_por">Inventariado por *</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                                <input type="text" id="inventariado_por" name="inventariado_por" required
                                       placeholder="Nombre del técnico" value="<?= htmlspecialchars($r['inventariado_por'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="inventariado_cc">Cédula inventariado</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-id-card"></i></span>
                                <input type="text" id="inventariado_cc" name="inventariado_cc"
                                       placeholder="Cédula" value="<?= htmlspecialchars($r['inventariado_cc'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="firma_cliente_cc">Cédula del cliente</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-id-card"></i></span>
                                <input type="text" id="firma_cliente_cc" name="firma_cliente_cc"
                                       placeholder="Cédula del cliente" value="<?= htmlspecialchars($r['firma_cliente_cc'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form__group">
                            <label for="autorizacion_adicional">Autorización adicional ($)</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-dollar-sign"></i></span>
                                <input type="number" id="autorizacion_adicional" name="autorizacion_adicional" min="0" step="0.01"
                                       placeholder="0" value="<?= htmlspecialchars($r['autorizacion_adicional'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form__actions">
                    <a href="/recepcion" class="btn btn--secondary">Cancelar</a>
                    <button type="submit" class="btn btn--primary">
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                        Guardar y continuar al checklist
                    </button>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
