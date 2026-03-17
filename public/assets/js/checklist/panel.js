(function () {
    var selectFecha = document.getElementById('checklistPanelFecha');
    var cuerpo = document.getElementById('checklistPanelCuerpo');
    var mensaje = document.getElementById('checklistPanelMensaje');
    var tablaWrap = document.getElementById('checklistPanelTablaWrap');
    var vacio = document.getElementById('checklistPanelVacio');

    if (!selectFecha || !cuerpo) return;

    function fechaHoy() {
        var d = new Date();
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    function formatearDmy(ymd) {
        if (!ymd || !/^\d{4}-\d{2}-\d{2}$/.test(ymd)) return ymd;
        var p = ymd.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }

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

    function etiquetaEstado(estado) {
        return estado === 'finalizada' ? 'Finalizada' : 'En proceso';
    }

    function cargarRevisiones(fecha) {
        ocultarMensaje();
        if (!fecha) {
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
                    var estadoLabel = etiquetaEstado(r.estado || 'en_proceso');
                    tr.innerHTML =
                        '<td>' + escapeHtml(r.placa) + '</td>' +
                        '<td>' + escapeHtml(r.encargado) + '</td>' +
                        '<td>' + escapeHtml(r.hora_inicio) + '</td>' +
                        '<td><span class="checklist-panel__estado checklist-panel__estado--' + (r.estado === 'finalizada' ? 'finalizada' : 'proceso') + '">' + escapeHtml(estadoLabel) + '</span></td>' +
                        '<td>' + (r.porcentaje_avance || 0) + '%</td>' +
                        '<td><a href="/checklist/detalle/' + (r.id || 0) + '" class="btn btn--secondary btn--small">Ver revisión</a></td>';
                    cuerpo.appendChild(tr);
                });
            })
            .catch(function () {
                mostrarMensaje('Error al cargar las revisiones.', true);
                mostrarTabla(false);
            });
    }

    fetch('/checklist/fechas-disponibles', { headers: { 'Accept': 'application/json' } })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            var fechas = data.fechas || [];
            var hoy = fechaHoy();
            if (fechas.indexOf(hoy) === -1) {
                fechas.unshift(hoy);
            }
            fechas.sort(function (a, b) { return b.localeCompare(a); });
            selectFecha.innerHTML = '<option value="">Seleccione una fecha</option>';
            fechas.forEach(function (f) {
                var opt = document.createElement('option');
                opt.value = f;
                opt.textContent = formatearDmy(f);
                selectFecha.appendChild(opt);
            });
            selectFecha.value = hoy;
            cargarRevisiones(hoy);
        })
        .catch(function () {
            var hoy = fechaHoy();
            selectFecha.innerHTML = '<option value="">Seleccione una fecha</option>' +
                '<option value="' + hoy + '">' + formatearDmy(hoy) + '</option>';
            selectFecha.value = hoy;
            cargarRevisiones(hoy);
            mostrarMensaje('No se pudieron cargar las fechas. Mostrando solo hoy.', true);
        });

    selectFecha.addEventListener('change', function () {
        cargarRevisiones(selectFecha.value.trim());
    });
})();
