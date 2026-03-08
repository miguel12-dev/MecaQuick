(function () {
    var selectFecha = document.getElementById('checklistPanelFecha');
    var cuerpo = document.getElementById('checklistPanelCuerpo');
    var mensaje = document.getElementById('checklistPanelMensaje');
    var tablaWrap = document.getElementById('checklistPanelTablaWrap');
    var vacio = document.getElementById('checklistPanelVacio');

    if (!selectFecha || !cuerpo) return;

    function mostrarMensaje(texto, esError) {
        if (!mensaje) return;
        mensaje.textContent = texto;
        mensaje.classList.remove('checklist-panel__mensaje--hidden');
        mensaje.classList.toggle('alert--error', esError);
        mensaje.classList.toggle('alert--success', !esError);
    }

    function ocultarMensaje() {
        if (mensaje) mensaje.classList.add('checklist-panel__mensaje--hidden');
    }

    function mostrarTabla(visible) {
        if (tablaWrap) tablaWrap.classList.toggle('checklist-panel__tabla-wrap--hidden', !visible);
        if (vacio) vacio.classList.toggle('checklist-panel__vacio--hidden', visible);
    }

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s == null ? '' : String(s);
        return div.innerHTML;
    }

    fetch('/checklist/fechas-disponibles', { headers: { 'Accept': 'application/json' } })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            var fechas = data.fechas || [];
            selectFecha.innerHTML = '<option value="">Seleccione una fecha</option>';
            fechas.forEach(function (f) {
                var opt = document.createElement('option');
                opt.value = f;
                opt.textContent = f;
                selectFecha.appendChild(opt);
            });
        })
        .catch(function () {
            mostrarMensaje('No se pudieron cargar las fechas disponibles.', true);
        });

    selectFecha.addEventListener('change', function () {
        var fecha = selectFecha.value.trim();
        ocultarMensaje();
        if (fecha === '') {
            cuerpo.innerHTML = '';
            mostrarTabla(false);
            return;
        }
        fetch('/checklist/revisiones?fecha=' + encodeURIComponent(fecha), { headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                var rev = data.revisiones || [];
                cuerpo.innerHTML = '';
                if (rev.length === 0) {
                    mostrarTabla(false);
                    return;
                }
                mostrarTabla(true);
                rev.forEach(function (r) {
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td>' + escapeHtml(r.placa) + '</td>' +
                        '<td>' + escapeHtml(r.encargado) + '</td>' +
                        '<td>' + escapeHtml(r.hora_inicio) + '</td>' +
                        '<td>' + (r.porcentaje_avance || 0) + '%</td>' +
                        '<td><a href="/checklist/detalle/' + (r.id || 0) + '" class="btn btn--secondary btn--small">Ver revisión</a></td>';
                    cuerpo.appendChild(tr);
                });
            })
            .catch(function () {
                mostrarMensaje('Error al cargar las revisiones.', true);
                mostrarTabla(false);
            });
    });
})();
