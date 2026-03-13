<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Mi Cuenta') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--cuenta">
        <section class="panel panel--cuenta">
            <h1 class="panel__title">Mi Cuenta</h1>

            <?php if (isset($_SESSION['alert'])): ?>
                <div class="alert alert--<?= htmlspecialchars($_SESSION['alert']['type']) ?>">
                    <?= htmlspecialchars($_SESSION['alert']['message']) ?>
                </div>
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>

            <div class="cuenta__grid">
                <div class="cuenta__card">
                    <h2 class="cuenta__card-title">
                        <i class="fas fa-user-circle"></i> Datos personales
                    </h2>
                    <form method="POST" action="/cuenta/actualizar" class="form">
                        <div class="form__field">
                            <label for="nombre" class="form__label">Nombre</label>
                            <input 
                                type="text" 
                                id="nombre" 
                                name="nombre" 
                                class="form__input" 
                                value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form__field">
                            <label for="correo" class="form__label">Correo electrónico</label>
                            <input 
                                type="email" 
                                id="correo" 
                                name="correo" 
                                class="form__input" 
                                value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form__field">
                            <label class="form__label">Rol</label>
                            <input 
                                type="text" 
                                class="form__input" 
                                value="<?= htmlspecialchars($usuario['rol'] ?? '') ?>"
                                disabled
                            >
                        </div>

                        <button type="submit" class="btn btn--primary">
                            <i class="fas fa-save"></i> Guardar cambios
                        </button>
                    </form>
                </div>

                <div class="cuenta__card">
                    <h2 class="cuenta__card-title">
                        <i class="fas fa-lock"></i> Cambiar contraseña
                    </h2>
                    <form method="POST" action="/cuenta/cambiar-password" class="form">
                        <div class="form__field">
                            <label for="password_actual" class="form__label">Contraseña actual</label>
                            <input 
                                type="password" 
                                id="password_actual" 
                                name="password_actual" 
                                class="form__input" 
                                required
                            >
                        </div>

                        <div class="form__field">
                            <label for="password_nueva" class="form__label">Nueva contraseña</label>
                            <input 
                                type="password" 
                                id="password_nueva" 
                                name="password_nueva" 
                                class="form__input" 
                                minlength="6"
                                required
                            >
                        </div>

                        <div class="form__field">
                            <label for="password_confirmar" class="form__label">Confirmar contraseña</label>
                            <input 
                                type="password" 
                                id="password_confirmar" 
                                name="password_confirmar" 
                                class="form__input" 
                                minlength="6"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn--primary">
                            <i class="fas fa-key"></i> Cambiar contraseña
                        </button>
                    </form>
                </div>
            </div>

            <div class="cuenta__actions">
                <a href="/dashboard" class="btn btn--secondary">
                    <i class="fas fa-arrow-left"></i> Volver al dashboard
                </a>
                <a href="/logout" class="btn btn--danger">
                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
