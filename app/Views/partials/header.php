<?php
$authUsuario = \App\Services\AuthService::getLoggedUser();
?>
<header class="header">
    <div class="header__inner">
        <a href="/" class="header__brand" aria-label="MecaQuick - Inicio">
            <img src="/assets/img/logo-sena_blanco.png" alt="MecaQuick" class="header__logo-img" width="40" height="40">
            <span class="header__title"><?= htmlspecialchars('MecaQuick') ?></span>
        </a>
        <nav class="header__nav">
            <?php if ($authUsuario !== null): ?>
                <a href="/dashboard" class="btn btn--header header__btn">
                    <i class="fas fa-th-large"></i>
                    Dashboard
                </a>
                <a href="/cuenta" class="header__user-link">
                    <i class="fas fa-user"></i>
                    <span><?= htmlspecialchars($authUsuario['nombre'] ?? 'Usuario') ?></span>
                </a>
            <?php else: ?>
                <a href="/login" class="btn btn--header header__btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar sesión
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>
