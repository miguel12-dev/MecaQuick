<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Iniciar sesión') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
</head>
<body class="page">
    <header class="header">
        <div class="header__inner">
            <a href="/" class="header__brand" aria-label="<?= htmlspecialchars($nombreSistema ?? 'MecaQuick') ?> - Inicio">
                <img src="/assets/img/logo_sena.png" alt="MecaQuick" class="header__logo-img" width="40" height="40">
                <span class="header__title"><?= htmlspecialchars($nombreSistema ?? 'MecaQuick') ?></span>
            </a>
            <nav class="header__nav">
                <a href="/" class="btn btn--secondary header__btn">Inicio</a>
                <a href="/home/formulario" class="btn btn--primary header__cta">Solicitar cita</a>
            </nav>
        </div>
    </header>

    <main class="layout layout--auth">
        <section class="panel panel--auth">
            <div class="auth__brand" aria-label="Identidad del sistema">
                <h1 class="auth__brand-title"><?= htmlspecialchars($nombreSistema ?? 'MecaQuick') ?></h1>
                <p class="auth__brand-caption">Acceso para personal y aprendices</p>
            </div>

            <hr class="auth__divider" aria-hidden="true">

            <?php if (!empty($error)): ?>
                <div class="alert alert--error" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="post" class="form form--auth">
                <div class="form__group">
                    <label for="email">Correo electrónico</label>
                    <div class="input-wrap">
                        <span class="input-wrap__icon" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span>
                        <input type="email" id="email" name="email" required autocomplete="email"
                               value="<?= htmlspecialchars($email ?? '') ?>" placeholder="ejemplo@sena.edu.co">
                    </div>
                </div>
                <div class="form__group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrap">
                        <span class="input-wrap__icon" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="********">
                    </div>
                </div>
                <div class="form__actions">
                    <button type="submit" class="btn btn--primary">
                        <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                        <span>Ingresar</span>
                    </button>
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
