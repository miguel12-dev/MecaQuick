<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'MecaQuick') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <?php require dirname(__DIR__, 2) . '/Views/partials/styles.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--form-page">
        <section class="panel panel--form">
            <h1 class="panel__title">
                <i class="fa-solid fa-clipboard-list panel__title-icon" aria-hidden="true"></i>
                Solicita tu cita
            </h1>
            <p class="panel__intro">Completa tus datos, elige una fecha disponible y recibe la confirmación por correo con horario y punto de encuentro.</p>

            <?php if (empty($sistemaActivo)): ?>
                <div class="alert alert--warning" role="alert">
                    El sistema de registro se encuentra temporalmente inactivo. Por favor inténtalo más tarde.
                </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="alert alert--error" role="alert">
                    <i class="fa-solid fa-circle-exclamation alert__icon" aria-hidden="true"></i>
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/home/registrar" method="post" class="form form--with-icons">
                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-user form__section-icon" aria-hidden="true"></i>
                        Datos personales
                    </h3>
                    <div class="form__section-fields">
                        <div class="form__group">
                            <label for="nombre">Nombre *</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                                <input type="text" id="nombre" name="nombre" required placeholder="Ej. Juan"
                                       value="<?= htmlspecialchars($old['nombre'] ?? '') ?>">
                            </div>
                            <span id="error-nombre" class="form__error"></span>
                        </div>
                        <div class="form__group">
                            <label for="apellido">Apellido *</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                                <input type="text" id="apellido" name="apellido" required placeholder="Ej. Pérez"
                                       value="<?= htmlspecialchars($old['apellido'] ?? '') ?>">
                            </div>
                            <span id="error-apellido" class="form__error"></span>
                        </div>
                        <div class="form__group form__group--full">
                            <label for="documento">Documento *</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-id-card"></i></span>
                                <input type="text" id="documento" name="documento" required placeholder="Número de documento"
                                       value="<?= htmlspecialchars($old['documento'] ?? '') ?>">
                            </div>
                            <span id="error-documento" class="form__error"></span>
                        </div>
                        <div class="form__group">
                            <label for="telefono">Teléfono</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" id="telefono" name="telefono" placeholder="Opcional"
                                       value="<?= htmlspecialchars($old['telefono'] ?? '') ?>">
                            </div>
                            <span id="error-telefono" class="form__error"></span>
                        </div>
                        <div class="form__group form__group--full">
                            <label for="email">Correo electrónico *</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com"
                                       value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                            </div>
                            <span id="error-email" class="form__error"></span>
                        </div>
                    </div>
                </div>

                <div class="form__section">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-car form__section-icon" aria-hidden="true"></i>
                        Datos del vehículo
                    </h3>
                    <div class="form__section-fields">
                        <div class="form__group">
                            <label for="placa">Placa *</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-rectangle-list"></i></span>
                                <input type="text" id="placa" name="placa" maxlength="10" required placeholder="Ej. ABC123"
                                       value="<?= htmlspecialchars($old['placa'] ?? '') ?>">
                            </div>
                            <span id="error-placa" class="form__error"></span>
                        </div>
                        <div class="form__group">
                            <label for="marca">Marca *</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-tag"></i></span>
                                <input type="text" id="marca" name="marca" required placeholder="Ej. Chevrolet, Mazda"
                                       value="<?= htmlspecialchars($old['marca'] ?? '') ?>">
                            </div>
                            <span id="error-marca" class="form__error"></span>
                        </div>
                        <div class="form__group">
                            <label for="modelo">Modelo</label>
                            <div class="input-wrap">
                                <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-car-side"></i></span>
                                <input type="text" id="modelo" name="modelo" placeholder="Opcional"
                                       value="<?= htmlspecialchars($old['modelo'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form__section form__section--full">
                    <h3 class="form__section-title">
                        <i class="fa-solid fa-calendar-days form__section-icon" aria-hidden="true"></i>
                        Cita y observaciones
                    </h3>
                    <div class="form__date-block">
                        <span id="fecha-label" class="form__date-label">Fecha de revisión *</span>
                        <div class="form__date-grid" role="group" aria-labelledby="fecha-label">
                            <?php
                            $oldFechaId = isset($old['fecha_id']) ? (int) $old['fecha_id'] : 0;
                            $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                            foreach ($fechas as $fecha):
                                $ts = strtotime($fecha['fecha']);
                                $diaSemana = $dias[(int) date('w', $ts)];
                                $textoFecha = date('d/m/Y', $ts);
                                $selected = $oldFechaId === (int) $fecha['id'];
                            ?>
                            <label class="form__date-card<?= $selected ? ' form__date-card--selected' : '' ?>">
                                <input type="radio" name="fecha_id" value="<?= (int) $fecha['id'] ?>" <?= $selected ? 'checked' : '' ?> required class="form__date-card-input">
                                <span class="form__date-card-day"><?= htmlspecialchars($diaSemana) ?></span>
                                <span class="form__date-card-date"><?= htmlspecialchars($textoFecha) ?></span>
                                <i class="fa-solid fa-calendar-check form__date-card-icon" aria-hidden="true"></i>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (empty($fechas)): ?>
                            <p class="form__help">No hay fechas disponibles por el momento. Intenta más tarde.</p>
                        <?php endif; ?>
                    </div>
                    <div class="form__section-fields">
                        <div class="form__group form__group--full">
                            <label for="observaciones">Observaciones adicionales</label>
                            <div class="input-wrap input-wrap--textarea">
                                <span class="input-wrap__icon input-wrap__icon--top" aria-hidden="true"><i class="fa-solid fa-comment-dots"></i></span>
                                <textarea id="observaciones" name="observaciones" rows="3" placeholder="Comentarios o indicaciones para la revisión"><?= htmlspecialchars($old['observaciones'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form__actions">
                    <button type="submit" class="btn btn--primary btn--with-icon" <?= empty($sistemaActivo) ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-paper-plane btn__icon" aria-hidden="true"></i>
                        Confirmar cita
                    </button>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.form');
        const inputs = {
            nombre: document.getElementById('nombre'),
            apellido: document.getElementById('apellido'),
            documento: document.getElementById('documento'),
            telefono: document.getElementById('telefono'),
            email: document.getElementById('email'),
            placa: document.getElementById('placa'),
            marca: document.getElementById('marca')
        };

        const validations = {
            nombre: (val) => val.trim().length > 0 || "El nombre es obligatorio",
            apellido: (val) => val.trim().length > 0 || "El apellido es obligatorio",
            documento: (val) => /^\d{6,10}$/.test(val) || "El documento debe tener entre 6 y 10 dígitos (solo números)",
            telefono: (val) => /^3\d{9}$/.test(val) || "El teléfono debe tener 10 dígitos y empezar por 3",
            email: (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || "Ingresa un correo electrónico válido",
            placa: (val) => /^[a-zA-Z]{3}\d{3}$/.test(val) || "La placa debe tener 3 letras seguidas de 3 números (Ej: ABC123)",
            marca: (val) => val.trim().length > 0 || "La marca es obligatoria"
        };

        // Strict numeric-only restriction for document and phone
        ['documento', 'telefono'].forEach(id => {
            const input = document.getElementById(id);
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });

        function validateField(name) {
            const input = inputs[name];
            const value = input.value;
            const result = validations[name](value);
            const errorSpan = document.getElementById(`error-${name}`);
            const wrap = input.closest('.input-wrap');

            if (result === true) {
                errorSpan.textContent = '';
                errorSpan.style.display = 'none';
                wrap.classList.remove('input-wrap--invalid');
                return true;
            } else {
                errorSpan.textContent = result;
                errorSpan.style.display = 'block';
                wrap.classList.add('input-wrap--invalid');
                return false;
            }
        }

        // Real-time validation
        Object.keys(inputs).forEach(name => {
            inputs[name].addEventListener('input', () => validateField(name));
            inputs[name].addEventListener('blur', () => validateField(name));
        });

        // Form submit validation
        form.addEventListener('submit', function(e) {
            let isValid = true;
            Object.keys(inputs).forEach(name => {
                if (!validateField(name)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = document.querySelector('.form__error[style*="display: block"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
    </script>
</body>
</html>
