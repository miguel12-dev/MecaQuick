<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Panel de revisión') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="/assets/js/checklist/panel.js" defer></script>
</head>
<body class="page">
    <?php require dirname(__DIR__, 2) . '/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard">
        <section class="panel panel--dashboard">
            <h1 class="panel__title">Revisión de checklists</h1>
            <p class="panel__intro">
                Filtre por día para ver las revisiones activas. Solo se muestran fechas con al menos una revisión.
            </p>

            <div class="checklist-panel__filtro">
                <label for="checklistPanelFecha" class="form__label">Fecha</label>
                <select id="checklistPanelFecha" class="form__control checklist-panel__select" aria-label="Seleccionar fecha">
                    <option value="">Seleccione una fecha</option>
                </select>
            </div>

            <div id="checklistPanelMensaje" class="checklist-panel__mensaje checklist-panel__mensaje--hidden" role="status"></div>

            <div class="checklist-panel__tabla-wrap" id="checklistPanelTablaWrap">
                <table class="checklist-panel__tabla" id="checklistPanelTabla" aria-label="Revisiones del día">
                    <thead>
                        <tr>
                            <th scope="col">Placa</th>
                            <th scope="col">Encargado</th>
                            <th scope="col">Hora inicio</th>
                            <th scope="col">Porcentaje</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="checklistPanelCuerpo">
                    </tbody>
                </table>
            </div>
            <p id="checklistPanelVacio" class="checklist-panel__vacio checklist-panel__vacio--hidden">
                No hay revisiones para la fecha seleccionada.
            </p>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Panel de revisión de checklists</span>
    </footer>
</body>
</html>
