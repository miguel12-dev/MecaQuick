<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Iniciar sesión') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <?php require dirname(__DIR__, 2) . '/Views/partials/styles.php'; ?>
    <link rel="stylesheet" href="/assets/css/auth/auth.css">
</head>
<body class="page page--auth">
    <div class="auth-container">
        <main class="auth-card">
            <header class="auth-card__header">
                <a href="/" class="auth-card__logo" aria-label="Volver al inicio">
                    <img src="/assets/img/logo_sena.png" alt="SENA Logo" class="auth-card__logo-img">
                </a>
                <h1 class="auth-card__title">MecaQuick</h1>
                <p class="auth-card__subtitle">Gestión de revisión mecánica</p>
            </header>

            <section class="auth-card__body">
                <h2 class="auth-card__heading">Iniciar sesión</h2>
                <p class="auth-card__intro">Ingresa tus credenciales para acceder al panel de control.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert--error auth-card__alert" role="alert">
                        <svg class="alert__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form action="/login" method="post" class="form auth-form">
                    <div class="form__group">
                        <label for="email" class="form__label">Correo electrónico</label>
                        <div class="form__input-wrapper">
                            <input type="email" id="email" name="email" class="form__input" required autocomplete="email"
                                   placeholder="ejemplo@sena.edu.co" value="<?= htmlspecialchars($email ?? '') ?>">
                        </div>
                    </div>
                    <div class="form__group">
                        <label for="password" class="form__label">Contraseña</label>
                        <div class="form__input-wrapper">
                            <input type="password" id="password" name="password" class="form__input" required autocomplete="current-password"
                                   placeholder="••••••••">
                        </div>
                    </div>
                    <div class="form__actions auth-form__actions">
                        <button type="submit" class="btn btn--primary">Entrar al sistema</button>
                    </div>
                </form>
            </section>

            <footer class="auth-card__footer">
                <a href="/" class="btn btn--secondary header__btn auth-card__back-link">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Volver al inicio
                </a>
            </footer>
        </main>
    </div>
</body>
</html>
