<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Nuevo usuario') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--usuarios">
        <section class="panel panel--usuarios">
            <h1 class="panel__title"><?= ($rol ?? '') === 'instructor' ? 'Nuevo instructor' : 'Nuevo aprendiz' ?></h1>
            <p class="panel__intro">Las credenciales (correo y contraseña) se enviarán al email indicado.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert--error" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="/usuarios/crear" method="post" class="form form--usuarios">
                <input type="hidden" name="rol" value="<?= htmlspecialchars($rol ?? 'aprendiz') ?>">
                <div class="form__group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required
                           value="<?= htmlspecialchars($old['nombre'] ?? '') ?>">
                </div>
                <div class="form__group">
                    <label for="email">Correo electrónico *</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                </div>
                <div class="form__group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
                    <span class="form__help">Mínimo 6 caracteres. Se enviará por correo al usuario.</span>
                </div>
                <div class="form__actions">
                    <button type="submit" class="btn btn--primary">Crear y enviar credenciales</button>
                    <a href="<?= ($rol ?? '') === 'instructor' ? '/usuarios/instructores' : '/usuarios/aprendices' ?>" class="btn btn--secondary">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
