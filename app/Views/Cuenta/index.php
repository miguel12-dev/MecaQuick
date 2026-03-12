<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Mi cuenta') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <?php require ROOT_PATH . '/app/Views/partials/styles.php'; ?>
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--dashboard layout--account">
        <section class="panel panel--dashboard">
            <header class="dashboard__header">
                <h1 class="panel__title">Mi cuenta</h1>
                <p class="panel__intro">
                    Gestiona tus datos de acceso y tu información personal.
                </p>
            </header>

            <section class="dashboard__grid" aria-label="Configuración de cuenta">
                <article class="dashboard-card">
                    <h2 class="dashboard-card__title">Datos de acceso</h2>
                    <p class="dashboard-card__text">
                        Consulta el correo con el que ingresas al sistema y tu rol asignado.
                    </p>
                    <div class="form form--account">
                        <div class="form__group">
                            <label>Correo electrónico de acceso</label>
                            <input type="email" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" readonly>
                        </div>
                        <div class="form__group">
                            <label>Rol en el sistema</label>
                            <input type="text" value="<?= htmlspecialchars($usuario['rol'] ?? '') ?>" readonly>
                        </div>
                    </div>
                </article>

                <article class="dashboard-card">
                    <h2 class="dashboard-card__title">Gestión de contraseña</h2>
                    <p class="dashboard-card__text">
                        Actualiza tu contraseña para mantener segura tu cuenta.
                    </p>
                    <form action="/cuenta/cambiar-password" method="post" class="form form--account">
                        <div class="form__group">
                            <label for="password_actual">Contraseña actual</label>
                            <input type="password" id="password_actual" name="password_actual" autocomplete="current-password">
                        </div>
                        <div class="form__group">
                            <label for="password_nueva">Nueva contraseña</label>
                            <input type="password" id="password_nueva" name="password_nueva" autocomplete="new-password">
                        </div>
                        <div class="form__group">
                            <label for="password_confirmacion">Confirmar nueva contraseña</label>
                            <input type="password" id="password_confirmacion" name="password_confirmacion" autocomplete="new-password">
                        </div>
                        <div class="form__actions">
                            <button type="submit" class="btn btn--primary">Guardar nueva contraseña</button>
                        </div>
                    </form>
                </article>

                <article class="dashboard-card">
                    <h2 class="dashboard-card__title">Datos personales</h2>
                    <p class="dashboard-card__text">
                        Mantén actualizado tu nombre y datos de contacto.
                    </p>
                    <form action="/cuenta/actualizar-perfil" method="post" class="form form--account">
                        <div class="form__group">
                            <label for="nombre">Nombre completo</label>
                            <input type="text" id="nombre" name="nombre"
                                   value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>">
                        </div>
                        <div class="form__group">
                            <label for="telefono">Teléfono (opcional)</label>
                            <input type="tel" id="telefono" name="telefono" value="">
                        </div>
                        <div class="form__actions">
                            <button type="submit" class="btn btn--primary">Guardar cambios</button>
                        </div>
                    </form>
                </article>
            </section>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
