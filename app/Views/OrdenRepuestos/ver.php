<?php
$o = $orden ?? [];
$items = $o['items'] ?? [];
unset($o['items']);
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

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <p class="panel__back">
                <a href="/orden-repuestos" class="btn btn--secondary">Volver al listado</a>
            </p>
            <h1 class="panel__title">Orden de repuestos #<?= (int) ($o['id'] ?? 0) ?></h1>

            <div class="checklist-detalle__cabecera">
                <dl class="checklist-detalle__meta">
                    <dt>Cliente</dt>
                    <dd><?= htmlspecialchars($o['cliente_nombre'] ?? '—') ?></dd>
                    <dt>Placa</dt>
                    <dd><?= htmlspecialchars($o['placa'] ?? '—') ?></dd>
                    <dt>Fecha entrada</dt>
                    <dd><?= !empty($o['fecha_entrada']) ? date('d/m/Y', strtotime($o['fecha_entrada'])) : '—' ?></dd>
                    <dt>Total</dt>
                    <dd>$ <?= number_format((float) ($o['total'] ?? 0), 0, ',', '.') ?></dd>
                </dl>
            </div>

            <h2 class="panel__subtitle">Información del cliente</h2>
            <p class="panel__text">
                <?= htmlspecialchars($o['cliente_nombre'] ?? '') ?> — NIT/C.C: <?= htmlspecialchars($o['cliente_documento'] ?? '') ?><br>
                <?= htmlspecialchars($o['cliente_direccion'] ?? '') ?> — <?= htmlspecialchars($o['cliente_ciudad'] ?? '') ?><br>
                Tel: <?= htmlspecialchars($o['cliente_telefono'] ?? '') ?> — Cel: <?= htmlspecialchars($o['cliente_celular'] ?? '') ?> — <?= htmlspecialchars($o['cliente_email'] ?? '') ?>
            </p>

            <h2 class="panel__subtitle">Información del vehículo</h2>
            <p class="panel__text">
                VIN: <?= htmlspecialchars($o['vin'] ?? '') ?> — No. Motor: <?= htmlspecialchars($o['no_motor'] ?? '') ?><br>
                Placa: <?= htmlspecialchars($o['placa'] ?? '') ?> — Modelo: <?= htmlspecialchars($o['modelo'] ?? '') ?><br>
                Color: <?= htmlspecialchars($o['color'] ?? '') ?> — Año: <?= htmlspecialchars($o['ano'] ?? '') ?><br>
                Fecha entrada: <?= !empty($o['fecha_entrada']) ? date('d/m/Y', strtotime($o['fecha_entrada'])) : '—' ?> <?= htmlspecialchars($o['hora_entrada'] ?? '') ?><br>
                Fecha prometida: <?= !empty($o['fecha_prometida']) ? date('d/m/Y', strtotime($o['fecha_prometida'])) : '—' ?> <?= htmlspecialchars($o['hora_prometida'] ?? '') ?><br>
                Mto KM: <?= htmlspecialchars($o['km_mto'] ?? '') ?> — Rep. Gral: <?= htmlspecialchars($o['rep_gral'] ?? '') ?>
            </p>

            <h2 class="panel__subtitle">Ítems</h2>
            <table class="panel-table">
                <thead>
                    <tr>
                        <th>Referencia</th>
                        <th>Descripción</th>
                        <th>Cant/Tiempo</th>
                        <th>$ Precio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['referencia'] ?? '') ?></td>
                        <td><?= htmlspecialchars($it['descripcion'] ?? '') ?></td>
                        <td><?= htmlspecialchars($it['cantidad_tiempo'] ?? '') ?></td>
                        <td>$ <?= number_format((float) ($it['precio_unitario'] ?? 0), 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="panel__text"><strong>TOTAL: $ <?= number_format((float) ($o['total'] ?? 0), 0, ',', '.') ?></strong></p>

            <p class="panel__text">
                Firma recepcionista: <?= htmlspecialchars($o['firma_recepcionista'] ?? '') ?> — C.C: <?= htmlspecialchars($o['cc_recepcionista'] ?? '') ?><br>
                Firma cliente: <?= htmlspecialchars($o['firma_cliente'] ?? '') ?> — C.C: <?= htmlspecialchars($o['cc_cliente'] ?? '') ?>
            </p>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
