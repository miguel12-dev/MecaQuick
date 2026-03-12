<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Iniciar sesión') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <?php require ROOT_PATH . '/app/Views/partials/styles.php'; ?>
</head>
<body class="page page--login">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--auth">
        <section class="panel panel--auth">
            <h1 class="panel__title">Iniciar sesión</h1>
            <p class="panel__intro">Acceso para usuarios del sistema (admin, instructor, aprendiz).</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert--error" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="post" class="form form--auth">
                <div class="form__group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" required autocomplete="email"
                           value="<?= htmlspecialchars($email ?? '') ?>">
                </div>
                <div class="form__group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <div class="form__actions">
                    <button type="submit" class="btn btn--primary">Entrar</button>
                    <a href="/" class="btn btn--secondary">Volver al inicio</a>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
