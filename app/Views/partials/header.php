<?php
$authUsuario = \App\Services\AuthService::getLoggedUser();
?>
<header class="header">
    <div class="header__inner">
        <a href="/" class="header__brand" aria-label="MecaQuick - Inicio">
            <img src="/assets/img/logo_sena.png" alt="MecaQuick" class="header__logo-img" width="40" height="40">
            <span class="header__title"><?= htmlspecialchars('MecaQuick') ?></span>
        </a>

        <div class="header__nav">
            <?php if ($authUsuario !== null): ?>
                <div class="header__user">
                    <div class="header__user-info">
                        <span class="header__user-label">Usuario</span>
                        <span class="header__user-name">
                            <?= htmlspecialchars($authUsuario['nombre'] ?? 'Usuario') ?>
                        </span>
                    </div>
                    <div class="header__user-actions">
                        <a href="/cuenta" class="header__user-account" aria-label="Ir a mi cuenta">
                            <span class="header__user-icon" aria-hidden="true"></span>
                        </a>
                        <a href="/logout" class="btn btn--secondary header__btn">Cerrar sesión</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/login" class="btn btn--secondary header__btn">Iniciar sesión</a>
            <?php endif; ?>
        </div>
    </div>
</header>
