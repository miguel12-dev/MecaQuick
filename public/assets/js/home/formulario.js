/**
 * Validaciones frontend del formulario de solicitud de cita.
 * Placa: 3 letras + 3 números, mayúsculas.
 * Documento: máx. 10 dígitos numéricos.
 * Año: obligatorio, rango razonable (1950 - año actual + 1).
 * Correo: formato estándar.
 * Nombre/Apellido: solo letras y espacios, sin caracteres especiales ni numéricos.
 * Teléfono: opcional.
 */
(function () {
    'use strict';

    const PLACA_REGEX = /^[A-Z]{3}[0-9]{3}$/;
    const DOCUMENTO_REGEX = /^[0-9]{1,10}$/;
    const NOMBRE_REGEX = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;
    const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const ANIO_MIN = 1950;
    const ANIO_MAX = new Date().getFullYear() + 1;

    const form = document.querySelector('.form--with-icons');
    if (!form || form.getAttribute('action') !== '/home/registrar') return;

    const campos = {
        nombre: document.getElementById('nombre'),
        apellido: document.getElementById('apellido'),
        documento: document.getElementById('documento'),
        telefono: document.getElementById('telefono'),
        email: document.getElementById('email'),
        placa: document.getElementById('placa'),
        marca: document.getElementById('marca'),
        modelo: document.getElementById('modelo'),
        anio: document.getElementById('anio'),
    };

    function getWrap(input) {
        return input ? input.closest('.input-wrap') : null;
    }

    function getGroup(input) {
        return input ? input.closest('.form__group') : null;
    }

    function marcarError(input, mensaje) {
        const wrap = getWrap(input);
        const group = getGroup(input);
        if (wrap) wrap.classList.add('input-wrap--invalid');
        input.setAttribute('aria-invalid', 'true');
        input.setAttribute('aria-describedby', input.id + '-error');
        let errEl = document.getElementById(input.id + '-error');
        if (!errEl) {
            errEl = document.createElement('span');
            errEl.id = input.id + '-error';
            errEl.className = 'form__error';
            errEl.setAttribute('role', 'alert');
            if (group) group.appendChild(errEl);
            else input.parentNode.appendChild(errEl);
        }
        errEl.textContent = mensaje;
    }

    function limpiarError(input) {
        const wrap = getWrap(input);
        if (wrap) wrap.classList.remove('input-wrap--invalid');
        input.removeAttribute('aria-invalid');
        input.removeAttribute('aria-describedby');
        const errEl = document.getElementById(input.id + '-error');
        if (errEl) errEl.textContent = '';
    }

    function validarPlaca(val) {
        const v = val.toUpperCase().replace(/\s/g, '');
        if (v.length !== 6) return 'La placa debe tener exactamente 6 caracteres: 3 letras y 3 números.';
        if (!PLACA_REGEX.test(v)) return 'Formato: 3 letras (A-Z) + 3 números (0-9). Ej: ABC123';
        return null;
    }

    function validarDocumento(val) {
        const v = val.replace(/\s/g, '');
        if (v.length === 0) return 'El documento es obligatorio.';
        if (v.length > 10) return 'Máximo 10 dígitos.';
        if (!DOCUMENTO_REGEX.test(v)) return 'Solo dígitos numéricos.';
        return null;
    }

    function validarNombre(val, etiqueta) {
        if (val.trim().length === 0) return etiqueta + ' es obligatorio.';
        if (!NOMBRE_REGEX.test(val)) return etiqueta + ': solo letras y espacios, sin números ni caracteres especiales.';
        return null;
    }

    function validarEmail(val) {
        if (val.trim().length === 0) return 'El correo es obligatorio.';
        if (!EMAIL_REGEX.test(val)) return 'Formato de correo inválido. Ej: correo@ejemplo.com';
        return null;
    }

    function validarAnio(val) {
        if (val.trim().length === 0) return 'El año del vehículo es obligatorio.';
        const n = parseInt(val, 10);
        if (isNaN(n)) return 'Debe ser un año válido.';
        if (n < ANIO_MIN || n > ANIO_MAX) return 'Año entre ' + ANIO_MIN + ' y ' + ANIO_MAX + '.';
        return null;
    }

    function validarTelefono(val) {
        if (val.trim().length === 0) return null;
        if (!/^[0-9+\-\s()]{7,15}$/.test(val)) return 'Formato de teléfono inválido.';
        return null;
    }

    function validarCampo(id, valor, validador) {
        const input = campos[id];
        if (!input) return true;
        const err = validador(valor);
        if (err) {
            marcarError(input, err);
            return false;
        }
        limpiarError(input);
        return true;
    }

    function normalizarPlaca(input) {
        let v = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        const letras = v.replace(/[0-9]/g, '');
        const numeros = v.replace(/[A-Z]/g, '');
        v = letras.slice(0, 3) + numeros.slice(0, 3);
        input.value = v;
    }

    function normalizarDocumento(input) {
        input.value = input.value.replace(/[^0-9]/g, '').slice(0, 10);
    }

    function setupCampo(id, validador, normalizador) {
        const input = campos[id];
        if (!input) return;

        if (normalizador) {
            input.addEventListener('input', function () { normalizador(input); });
            input.addEventListener('blur', function () { normalizador(input); });
        }

        input.addEventListener('blur', function () {
            validarCampo(id, input.value, validador);
        });
    }

    setupCampo('placa', validarPlaca, normalizarPlaca);
    setupCampo('documento', validarDocumento, normalizarDocumento);
    setupCampo('nombre', function (v) { return validarNombre(v, 'Nombre'); });
    setupCampo('apellido', function (v) { return validarNombre(v, 'Apellido'); });
    setupCampo('email', validarEmail);
    setupCampo('telefono', validarTelefono);
    if (campos.anio) setupCampo('anio', validarAnio);

    form.addEventListener('submit', function (e) {
        let valido = true;

        valido = validarCampo('nombre', campos.nombre?.value ?? '', function (v) { return validarNombre(v, 'Nombre'); }) && valido;
        valido = validarCampo('apellido', campos.apellido?.value ?? '', function (v) { return validarNombre(v, 'Apellido'); }) && valido;
        valido = validarCampo('documento', campos.documento?.value ?? '', validarDocumento) && valido;
        valido = validarCampo('email', campos.email?.value ?? '', validarEmail) && valido;
        valido = validarCampo('telefono', campos.telefono?.value ?? '', validarTelefono) && valido;

        normalizarPlaca(campos.placa);
        valido = validarCampo('placa', campos.placa?.value ?? '', validarPlaca) && valido;

        if (campos.anio) {
            valido = validarCampo('anio', campos.anio?.value ?? '', validarAnio) && valido;
        }

        if (!valido) {
            e.preventDefault();
            const primerError = form.querySelector('[aria-invalid="true"]');
            if (primerError) primerError.focus();
        }
    });
})();
