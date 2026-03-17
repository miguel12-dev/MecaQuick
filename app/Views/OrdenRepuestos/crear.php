<?php
$orden = $orden ?? [];
$items = $items ?? [];
$usuario = $usuario ?? null;
$ordenId = (int) ($orden['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Orden de repuestos') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--form-page">
        <section class="panel panel--form">
            <p class="panel__back">
                <a href="/orden-repuestos" class="btn btn--secondary">Volver a órdenes</a>
            </p>
            <h1 class="panel__title">Orden de repuestos</h1>

            <form action="/orden-repuestos/guardar" method="post" class="orden-repuestos-form">
                <input type="hidden" name="orden_id" value="<?= $ordenId ?>">

                <h2 class="panel__subtitle">Información del cliente</h2>
                <div class="checklist-grid checklist-grid--cabecera">
                    <div class="form__group form__group--full">
                        <label for="cliente_nombre">Nombre</label>
                        <input type="text" id="cliente_nombre" name="cliente_nombre" value="<?= htmlspecialchars($orden['cliente_nombre'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_documento">NIT / C.C.</label>
                        <input type="text" id="cliente_documento" name="cliente_documento" value="<?= htmlspecialchars($orden['cliente_documento'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_direccion">Dirección</label>
                        <input type="text" id="cliente_direccion" name="cliente_direccion" value="<?= htmlspecialchars($orden['cliente_direccion'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_ciudad">Ciudad</label>
                        <input type="text" id="cliente_ciudad" name="cliente_ciudad" value="<?= htmlspecialchars($orden['cliente_ciudad'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_telefono">Teléfono</label>
                        <input type="text" id="cliente_telefono" name="cliente_telefono" value="<?= htmlspecialchars($orden['cliente_telefono'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_celular">Celular</label>
                        <input type="text" id="cliente_celular" name="cliente_celular" value="<?= htmlspecialchars($orden['cliente_celular'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cliente_email">E-mail</label>
                        <input type="email" id="cliente_email" name="cliente_email" value="<?= htmlspecialchars($orden['cliente_email'] ?? '') ?>">
                    </div>
                </div>

                <h2 class="panel__subtitle" style="margin-top: 1.25rem;">Información del vehículo</h2>
                <div class="checklist-grid checklist-grid--cabecera">
                    <div class="form__group">
                        <label for="vin">VIN</label>
                        <input type="text" id="vin" name="vin" value="<?= htmlspecialchars($orden['vin'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="numero_motor">No. Motor</label>
                        <input type="text" id="numero_motor" name="numero_motor" value="<?= htmlspecialchars($orden['numero_motor'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="placa">Placa</label>
                        <input type="text" id="placa" name="placa" value="<?= htmlspecialchars($orden['placa'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="modelo">Modelo</label>
                        <input type="text" id="modelo" name="modelo" value="<?= htmlspecialchars($orden['modelo'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" value="<?= htmlspecialchars($orden['color'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="ano">Año</label>
                        <input type="number" id="ano" name="ano" min="1950" max="2030" value="<?= htmlspecialchars($orden['ano'] ?? '') ?>">
                    </div>
                </div>

                <h2 class="panel__subtitle" style="margin-top: 1.25rem;">Fechas y datos adicionales</h2>
                <div class="checklist-grid checklist-grid--cabecera">
                    <div class="form__group">
                        <label for="fecha_entrada">Fecha entrada</label>
                        <input type="date" id="fecha_entrada" name="fecha_entrada" value="<?= htmlspecialchars($orden['fecha_entrada'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="form__group">
                        <label for="hora_entrada">Hora</label>
                        <input type="time" id="hora_entrada" name="hora_entrada" value="<?= htmlspecialchars($orden['hora_entrada'] ?? date('H:i')) ?>">
                    </div>
                    <div class="form__group">
                        <label for="fecha_prometida">Fecha prometida de entrega</label>
                        <input type="date" id="fecha_prometida" name="fecha_prometida" value="<?= htmlspecialchars($orden['fecha_prometida'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="hora_prometida">Hora</label>
                        <input type="time" id="hora_prometida" name="hora_prometida" value="<?= htmlspecialchars($orden['hora_prometida'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="mto_km">Mto KM</label>
                        <input type="number" id="mto_km" name="mto_km" min="0" value="<?= htmlspecialchars($orden['mto_km'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="rep_gral">Rep. Gral.</label>
                        <input type="text" id="rep_gral" name="rep_gral" value="<?= htmlspecialchars($orden['rep_gral'] ?? '') ?>">
                    </div>
                </div>

                <h2 class="panel__subtitle" style="margin-top: 1.25rem;">Referencia — Descripción — Cant/Tiempo — $ Precio</h2>
                <div class="orden-repuestos-items">
                    <table class="panel-table">
                        <thead>
                            <tr>
                                <th scope="col">REFERENCIA</th>
                                <th scope="col">DESCRIPCIÓN</th>
                                <th scope="col">CANT/TIEMPO</th>
                                <th scope="col">$ PRECIO</th>
                            </tr>
                        </thead>
                        <tbody id="ordenRepuestosItemsBody">
                            <?php
                            $rows = count($items) > 0 ? $items : [['referencia' => '', 'descripcion' => '', 'cant_tiempo' => '', 'precio' => '']];
                            if (count($rows) < 10) {
                                $rows = array_pad($rows, 10, ['referencia' => '', 'descripcion' => '', 'cant_tiempo' => '', 'precio' => '']);
                            }
                            foreach ($rows as $i => $it):
                            ?>
                            <tr>
                                <td><input type="text" name="items_referencia[]" value="<?= htmlspecialchars($it['referencia'] ?? '') ?>" class="form__control"></td>
                                <td><input type="text" name="items_descripcion[]" value="<?= htmlspecialchars($it['descripcion'] ?? '') ?>" class="form__control"></td>
                                <td><input type="text" name="items_cant_tiempo[]" value="<?= htmlspecialchars($it['cant_tiempo'] ?? '') ?>" placeholder="Cant." class="form__control"></td>
                                <td><input type="number" name="items_precio[]" value="<?= htmlspecialchars($it['precio'] ?? '') ?>" min="0" step="0.01" placeholder="0" class="form__control"></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h2 class="panel__subtitle" style="margin-top: 1.25rem;">Firmas</h2>
                <div class="checklist-grid checklist-grid--cabecera">
                    <div class="form__group">
                        <label for="firma_recepcionista">Firma recepcionista</label>
                        <input type="text" id="firma_recepcionista" name="firma_recepcionista" value="<?= htmlspecialchars($orden['firma_recepcionista'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cc_recepcionista">C.C. recepcionista</label>
                        <input type="text" id="cc_recepcionista" name="cc_recepcionista" value="<?= htmlspecialchars($orden['cc_recepcionista'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="firma_cliente">Firma cliente</label>
                        <input type="text" id="firma_cliente" name="firma_cliente" value="<?= htmlspecialchars($orden['firma_cliente'] ?? '') ?>">
                    </div>
                    <div class="form__group">
                        <label for="cc_cliente">C.C. cliente</label>
                        <input type="text" id="cc_cliente" name="cc_cliente" value="<?= htmlspecialchars($orden['cc_cliente'] ?? '') ?>">
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
