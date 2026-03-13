<?php
$errores = $_SESSION['orden_repuestos_errores'] ?? [];
$datos = $datos ?? $_SESSION['orden_repuestos_datos'] ?? [];
$items = $datos['items'] ?? [];
unset($datos['items']);
$inspeccionId = (int) ($inspeccion_id ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Nueva orden de repuestos') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="/assets/js/orden-repuestos.js" defer></script>
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--form-page">
        <section class="panel panel--form">
            <h1 class="panel__title">Orden de repuestos</h1>
            <p class="panel__intro">
                <?php if ($inspeccionId > 0): ?>
                    Los datos del cliente y vehículo se han precargado desde la inspección. Complete los ítems y firmas.
                <?php else: ?>
                    Complete los datos del cliente, vehículo y los ítems de la orden.
                <?php endif; ?>
            </p>

            <div class="dashboard__actions" style="margin-bottom: 1rem;">
                <a href="/orden-repuestos" class="btn btn--secondary">Volver al listado</a>
                <?php if ($inspeccionId > 0): ?>
                    <a href="/recepcion/revision/<?= $inspeccionId ?>" class="btn btn--secondary">Ver revisión</a>
                <?php endif; ?>
            </div>

            <?php if ($errores !== []): ?>
                <div class="alert alert--error" role="alert">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        <?php foreach ($errores as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/orden-repuestos/guardar" method="post" class="orden-repuestos-form">
                <input type="hidden" name="inspeccion_id" value="<?= $inspeccionId ?>">

                <h2 class="panel__subtitle">Información del cliente</h2>
                <div class="checklist-grid checklist-grid--cabecera">
                    <div class="form__group form__group--full">
                        <label for="cliente_nombre">Nombre *</label>
                        <input type="text" id="cliente_nombre" name="cliente_nombre" required value="<?= htmlspecialchars($datos['cliente_nombre'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_documento">NIT / C.C. *</label>
                        <input type="text" id="cliente_documento" name="cliente_documento" required value="<?= htmlspecialchars($datos['cliente_documento'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_direccion">Dirección</label>
                        <input type="text" id="cliente_direccion" name="cliente_direccion" value="<?= htmlspecialchars($datos['cliente_direccion'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_ciudad">Ciudad</label>
                        <input type="text" id="cliente_ciudad" name="cliente_ciudad" value="<?= htmlspecialchars($datos['cliente_ciudad'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_telefono">Teléfono</label>
                        <input type="text" id="cliente_telefono" name="cliente_telefono" value="<?= htmlspecialchars($datos['cliente_telefono'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_celular">Celular</label>
                        <input type="text" id="cliente_celular" name="cliente_celular" value="<?= htmlspecialchars($datos['cliente_celular'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_email">E-mail</label>
                        <input type="email" id="cliente_email" name="cliente_email" value="<?= htmlspecialchars($datos['cliente_email'] ?? '') ?>">
                    </div>
                </div>

                <h2 class="panel__subtitle" style="margin-top: 1.25rem;">Información del vehículo</h2>
                <div class="checklist-grid checklist-grid--cabecera">
                    <div class="form__group">
                        <label for="vin">VIN</label>
                        <input type="text" id="vin" name="vin" value="<?= htmlspecialchars($datos['vin'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="no_motor">No. Motor</label>
                        <input type="text" id="no_motor" name="no_motor" value="<?= htmlspecialchars($datos['no_motor'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="placa">Placa *</label>
                        <input type="text" id="placa" name="placa" required value="<?= htmlspecialchars($datos['placa'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="modelo">Modelo</label>
                        <input type="text" id="modelo" name="modelo" value="<?= htmlspecialchars($datos['modelo'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" value="<?= htmlspecialchars($datos['color'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="ano">Año</label>
                        <input type="number" id="ano" name="ano" min="1950" max="2030" value="<?= htmlspecialchars($datos['ano'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="fecha_entrada">Fecha entrada *</label>
                        <input type="date" id="fecha_entrada" name="fecha_entrada" required value="<?= htmlspecialchars($datos['fecha_entrada'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="form__group">
                        <label for="hora_entrada">Hora</label>
                        <input type="time" id="hora_entrada" name="hora_entrada" value="<?= htmlspecialchars($datos['hora_entrada'] ?? date('H:i')) ?>">
                    </div>
                    <div class="form__group">
                        <label for="fecha_prometida">Fecha prometida de entrega</label>
                        <input type="date" id="fecha_prometida" name="fecha_prometida" value="<?= htmlspecialchars($datos['fecha_prometida'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="hora_prometida">Hora</label>
                        <input type="time" id="hora_prometida" name="hora_prometida" value="<?= htmlspecialchars($datos['hora_prometida'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="km_mto">Mto KM</label>
                        <input type="number" id="km_mto" name="km_mto" min="0" value="<?= htmlspecialchars($datos['km_mto'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="rep_gral">Rep. Gral.</label>
                        <input type="text" id="rep_gral" name="rep_gral" value="<?= htmlspecialchars($datos['rep_gral'] ?? '') ?>">
                    </div>
                </div>

                <h2 class="panel__subtitle" style="margin-top: 1.25rem;">Ítems de la orden</h2>
                <table class="panel-table orden-repuestos-items">
                    <thead>
                        <tr>
                            <th>Referencia</th>
                            <th>Descripción</th>
                            <th>Cant/Tiempo</th>
                            <th>$ Precio</th>
                        </tr>
                    </thead>
                    <tbody id="ordenItemsBody">
                        <?php
                        $rows = count($items) > 0 ? $items : [['referencia' => '', 'descripcion' => '', 'cantidad_tiempo' => '', 'precio_unitario' => '']];
                        foreach ($rows as $idx => $it):
                        ?>
                        <tr>
                            <td><input type="text" name="item_referencia[]" placeholder="Ref." value="<?= htmlspecialchars($it['referencia'] ?? '') ?>"></td>
                            <td><input type="text" name="item_descripcion[]" placeholder="Descripción" value="<?= htmlspecialchars($it['descripcion'] ?? '') ?>"></td>
                            <td><input type="text" name="item_cantidad[]" placeholder="Cant." value="<?= htmlspecialchars($it['cantidad_tiempo'] ?? '') ?>"></td>
                            <td><input type="number" name="item_precio[]" min="0" step="0.01" placeholder="0" value="<?= htmlspecialchars($it['precio_unitario'] ?? '') ?>"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" id="btnAgregarItem" class="btn btn--secondary" style="margin-top: 0.5rem;">
                    <i class="fas fa-plus"></i> Agregar fila
                </button>

                <h2 class="panel__subtitle" style="margin-top: 1.25rem;">Firmas</h2>
                <div class="checklist-grid checklist-grid--cabecera">
                    <div class="form__group">
                        <label for="firma_recepcionista">Firma recepcionista</label>
                        <input type="text" id="firma_recepcionista" name="firma_recepcionista" value="<?= htmlspecialchars($datos['firma_recepcionista'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cc_recepcionista">C.C. recepcionista</label>
                        <input type="text" id="cc_recepcionista" name="cc_recepcionista" value="<?= htmlspecialchars($datos['cc_recepcionista'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="firma_cliente">Firma cliente</label>
                        <input type="text" id="firma_cliente" name="firma_cliente" value="<?= htmlspecialchars($datos['firma_cliente'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cc_cliente">C.C. cliente</label>
                        <input type="text" id="cc_cliente" name="cc_cliente" value="<?= htmlspecialchars($datos['cc_cliente'] ?? '') ?>">
                    </div>
                </div>

                <div class="form__actions" style="margin-top: 1.25rem;">
                    <a href="/orden-repuestos" class="btn btn--secondary">Cancelar</a>
                    <button type="submit" class="btn btn--primary">Guardar orden</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
