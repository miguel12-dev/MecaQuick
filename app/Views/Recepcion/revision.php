<?php
$d = $detalle ?? [];
$resultados = $d['resultados'] ?? [];
$placa = htmlspecialchars($d['placa'] ?? '—');
$encargado = htmlspecialchars($d['encargado'] ?? 'Sin asignar');
$horaInicio = isset($d['inicio_at']) ? date('H:i', strtotime($d['inicio_at'])) : '—';
$fechaInicio = isset($d['inicio_at']) ? date('d/m/Y', strtotime($d['inicio_at'])) : '—';
$porcentaje = (int) ($d['porcentaje_avance'] ?? 0);
$estado = $d['estado'] ?? 'en_proceso';
$estadoLabel = $estado === 'finalizada' ? 'Finalizada' : 'En proceso';
$ayudantes = $ayudantes ?? [];
$mostrarFormAyudantes = $mostrarFormAyudantes ?? false;
$listaAprendices = $listaAprendices ?? [];
$inspeccionId = (int) ($inspeccion_id ?? 0);
$tieneOrden = $tieneOrden ?? false;
$ordenRepuestosId = (int) ($ordenRepuestosId ?? 0);
$mostrarLinkOrden = $mostrarLinkOrden ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Detalle de revisión') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <p class="panel__back">
                <a href="/recepcion/mis-revisiones" class="btn btn--secondary">Volver a mis revisiones</a>
            </p>
            <h1 class="panel__title">Detalle de revisión</h1>

            <div class="checklist-detalle__cabecera">
                <dl class="checklist-detalle__meta">
                    <dt>Placa</dt>
                    <dd><?= $placa ?></dd>
                    <dt>Encargado</dt>
                    <dd><?= $encargado ?></dd>
                    <dt>Fecha</dt>
                    <dd><?= $fechaInicio ?></dd>
                    <dt>Hora inicio</dt>
                    <dd><?= $horaInicio ?></dd>
                    <dt>Avance</dt>
                    <dd><?= $porcentaje ?>%</dd>
                    <dt>Estado</dt>
                    <dd><?= htmlspecialchars($estadoLabel) ?></dd>
                </dl>
            </div>

            <?php if ($mostrarLinkOrden && !$tieneOrden): ?>
                <div class="panel__block">
                    <a href="/orden-repuestos/crear/<?= $inspeccionId ?>" class="btn btn--primary">
                        <i class="fas fa-box-open"></i> Crear orden de repuestos
                    </a>
                </div>
            <?php elseif ($mostrarLinkOrden && $tieneOrden): ?>
                <div class="panel__block dashboard__actions">
                    <a href="/orden-repuestos/editar/<?= $ordenRepuestosId ?>" class="btn btn--primary">
                        <i class="fas fa-edit"></i> Ver/Editar orden de repuestos
                    </a>
                    <a href="/orden-repuestos" class="btn btn--secondary">Ver todas las órdenes</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($d['observaciones_generales'])): ?>
                <div class="panel__block">
                    <p class="panel__subtitle">Observaciones generales del mantenimiento</p>
                    <p class="panel__text"><?= nl2br(htmlspecialchars($d['observaciones_generales'])) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($ayudantes !== []): ?>
                <p class="panel__subtitle">Integrantes del grupo</p>
                <ul class="panel__list">
                    <?php foreach ($ayudantes as $a): ?>
                        <li><?= htmlspecialchars($a['nombre'] ?? '') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="checklist-detalle__tabla-wrap">
                <table class="checklist-detalle__tabla" aria-label="Puntos de la revisión">
                    <thead>
                        <tr>
                            <th scope="col">Punto</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Valor medido</th>
                            <th scope="col">Observación</th>
                            <th scope="col">Evidencias</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $r): ?>
                        <tr>
                            <td><?= (int) ($r['numero_punto'] ?? 0) ?></td>
                            <td><?= htmlspecialchars($r['punto_descripcion'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['estado'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['valor_medido'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($r['observacion'] ?? '—') ?></td>
                            <td>
                                <?php
                                $evidencias = $r['evidencias'] ?? [];
                                if ($evidencias !== []): ?>
                                    <?php foreach ($evidencias as $ruta): ?>
                                        <a href="<?= htmlspecialchars($ruta) ?>" target="_blank" rel="noopener">Ver</a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($mostrarFormAyudantes && $inspeccionId > 0): ?>
                <div class="ayudantes-form">
                    <p class="panel__subtitle">Agregar ayudantes o integrantes del grupo</p>
                    <p class="panel__text">Seleccione los aprendices que participaron en esta revisión. Usted ya figura como responsable.</p>
                    <form action="/recepcion/agregar-ayudantes" method="post">
                        <input type="hidden" name="inspeccion_id" value="<?= $inspeccionId ?>">
                        <?php if ($listaAprendices !== []): ?>
                            <ul class="ayudantes-form__list">
                                <?php foreach ($listaAprendices as $ap): ?>
                                    <li>
                                        <label>
                                            <input type="checkbox" name="ayudantes[]" value="<?= (int) $ap['id'] ?>">
                                            <?= htmlspecialchars($ap['nombre'] ?? '') ?>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="form__actions" style="margin-top: 0.75rem;">
                                <button type="submit" class="btn btn--primary">Guardar ayudantes</button>
                            </div>
                        <?php else: ?>
                            <p class="panel__text">No hay otros aprendices registrados para agregar.</p>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
