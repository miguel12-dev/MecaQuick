<?php
$d = $detalle ?? [];
$resultados = $d['resultados'] ?? [];
$inspeccionId = (int) ($d['id'] ?? 0);
$placa = htmlspecialchars($d['placa'] ?? '—');
$encargado = htmlspecialchars($d['encargado'] ?? 'Sin asignar');
$horaInicio = isset($d['inicio_at']) ? date('H:i', strtotime($d['inicio_at'])) : '—';
$porcentaje = (int) ($d['porcentaje_avance'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Detalle de revisión') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
    <?php require dirname(__DIR__, 2) . '/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <p class="panel__back">
                <a href="/checklist/panel" class="btn btn--secondary">Volver al panel</a>
            </p>
            <h1 class="panel__title">Detalle de revisión</h1>

            <div class="checklist-detalle__cabecera" id="checklistDetalleCabecera">
                <dl class="checklist-detalle__meta">
                    <dt>Placa</dt>
                    <dd id="checklistDetallePlaca"><?= $placa ?></dd>
                    <dt>Encargado</dt>
                    <dd id="checklistDetalleEncargado"><?= $encargado ?></dd>
                    <dt>Hora inicio</dt>
                    <dd id="checklistDetalleHora"><?= $horaInicio ?></dd>
                    <dt>Avance</dt>
                    <dd id="checklistDetallePorcentaje"><?= $porcentaje ?>%</dd>
                </dl>
            </div>

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
                    <tbody id="checklistDetalleCuerpo">
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
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Detalle de revisión</span>
    </footer>

    <script>
    (function () {
        var inspeccionId = <?= json_encode($inspeccionId) ?>;
        if (inspeccionId < 1) return;
        var detalleUrl = '/checklist/detalle/' + inspeccionId;
        var intervalMs = 25000;

        function actualizar() {
            fetch(detalleUrl + '?ajax=1', { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (data) {
                    if (!data) return;
                    var placaEl = document.getElementById('checklistDetallePlaca');
                    var encargadoEl = document.getElementById('checklistDetalleEncargado');
                    var horaEl = document.getElementById('checklistDetalleHora');
                    var pctEl = document.getElementById('checklistDetallePorcentaje');
                    if (placaEl) placaEl.textContent = data.placa || '—';
                    if (encargadoEl) encargadoEl.textContent = data.encargado || 'Sin asignar';
                    if (horaEl) horaEl.textContent = data.hora_inicio || '—';
                    if (pctEl) pctEl.textContent = (data.porcentaje_avance || 0) + '%';

                    var tbody = document.getElementById('checklistDetalleCuerpo');
                    if (!tbody || !Array.isArray(data.resultados)) return;
                    tbody.innerHTML = data.resultados.map(function (r) {
                        var evLinks = (r.evidencias || []).map(function (url) {
                            return '<a href="' + escapeHtml(url) + '" target="_blank" rel="noopener">Ver</a>';
                        }).join(' ');
                        return '<tr><td>' + (r.numero_punto || '') + '</td><td>' + escapeHtml(r.descripcion || '') +
                            '</td><td>' + escapeHtml(r.estado || '') + '</td><td>' + escapeHtml(r.valor_medido || '—') +
                            '</td><td>' + escapeHtml(r.observacion || '—') + '</td><td>' + (evLinks || '—') + '</td></tr>';
                    }).join('');
                });
        }
        function escapeHtml(s) {
            var div = document.createElement('div');
            div.textContent = s;
            return div.innerHTML;
        }
        setInterval(actualizar, intervalMs);
    })();
    </script>
</body>
</html>
