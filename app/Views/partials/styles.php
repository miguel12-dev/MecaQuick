<?php
/**
 * Carga de estilos en orden. Usar <link> en lugar de @import evita FOUC
 * y garantiza carga determinística en cada recarga.
 */
$cssBase = '/assets/css/';
$stylesheets = [
    'base.css',
    'layout/layout.css',
    'header/header.css',
    'panel/panel.css',
    'form/form.css',
    'buttons/buttons.css',
    'alerts/alerts.css',
    'footer/footer.css',
    'home/home.css',
    'checklist/checklist.css',
    'recepcion/recepcion.css',
];
foreach ($stylesheets as $file) {
    echo '<link rel="stylesheet" href="' . htmlspecialchars($cssBase . $file) . '">' . "\n";
}
