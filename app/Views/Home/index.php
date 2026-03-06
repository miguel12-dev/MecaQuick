<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'MecaQuick') ?></title>
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
    <header class="header">
        <div class="header__brand">
            <span class="header__logo">MecaQuick</span>
            <span class="header__title"><?= htmlspecialchars($nombreSistema ?? 'MecaQuick') ?></span>
        </div>
    </header>

    <main class="layout">
        <section class="panel panel--info">
            <h1 class="panel__title">Programa de revisión mecánica</h1>
            <p class="panel__text">
                Sistema de gestión de turnos para la revisión mecánica de vehículos en el centro de tecnología automotriz.
                Registra tus datos, selecciona una fecha disponible y recibe la confirmación de tu cita por correo electrónico.
            </p>
            <ul class="panel__list">
                <li>Atención programada por cupos diarios controlados.</li>
                <li>Registro de datos del propietario y del vehículo.</li>
                <li>Asignación de fecha de revisión según disponibilidad.</li>
            </ul>
            <?php if (empty($sistemaActivo)): ?>
                <p class="alert alert--warning">
                    El sistema de registro se encuentra temporalmente inactivo. Por favor inténtalo más tarde.
                </p>
            <?php endif; ?>
        </section>

        <section class="panel panel--form">
            <h2 class="panel__title">Solicita tu cita</h2>

            <?php if (!empty($errores)): ?>
                <div class="alert alert--error">
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/home/registrar" method="post" class="form">
                <div class="form__group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required
                           value="<?= htmlspecialchars($old['nombre'] ?? '') ?>">
                </div>
                <div class="form__group">
                    <label for="apellido">Apellido *</label>
                    <input type="text" id="apellido" name="apellido" required
                           value="<?= htmlspecialchars($old['apellido'] ?? '') ?>">
                </div>
                <div class="form__group">
                    <label for="documento">Documento *</label>
                    <input type="text" id="documento" name="documento" required
                           value="<?= htmlspecialchars($old['documento'] ?? '') ?>">
                </div>
                <div class="form__group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono"
                           value="<?= htmlspecialchars($old['telefono'] ?? '') ?>">
                </div>
                <div class="form__group">
                    <label for="email">Correo electrónico *</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                </div>

                <div class="form__group">
                    <label for="placa">Placa *</label>
                    <input type="text" id="placa" name="placa" maxlength="10" required
                           value="<?= htmlspecialchars($old['placa'] ?? '') ?>">
                </div>
                <div class="form__group">
                    <label for="marca">Marca *</label>
                    <input type="text" id="marca" name="marca" required
                           value="<?= htmlspecialchars($old['marca'] ?? '') ?>">
                </div>
                <div class="form__group">
                    <label for="modelo">Modelo *</label>
                    <input type="text" id="modelo" name="modelo" required
                           value="<?= htmlspecialchars($old['modelo'] ?? '') ?>">
                </div>
                <div class="form__group">
                    <label for="anio">Año</label>
                    <input type="number" id="anio" name="anio" min="1950" max="<?= (int) date('Y') + 1 ?>"
                           value="<?= htmlspecialchars($old['anio'] ?? '') ?>">
                </div>

                <div class="form__group">
                    <label for="fecha_id">Fecha de revisión *</label>
                    <select id="fecha_id" name="fecha_id" required>
                        <option value="">Selecciona una fecha</option>
                        <?php foreach ($fechas as $fecha): ?>
                            <option
                                value="<?= (int) $fecha['id'] ?>"
                                <?= !$fecha['disponible'] ? 'disabled' : '' ?>
                                <?= (isset($old['fecha_id']) && (int) $old['fecha_id'] === (int) $fecha['id']) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($fecha['label']) ?>
                                <?= !$fecha['disponible'] ? ' - Sin cupos' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form__help">
                        Las fechas sin cupos se muestran deshabilitadas, pero visibles para referencia.
                    </small>
                </div>

                <div class="form__group">
                    <label for="observaciones">Observaciones adicionales</label>
                    <textarea id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars($old['observaciones'] ?? '') ?></textarea>
                </div>

                <div class="form__actions">
                    <button type="submit" class="btn btn--primary" <?= empty($sistemaActivo) ? 'disabled' : '' ?>>
                        Confirmar cita
                    </button>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
