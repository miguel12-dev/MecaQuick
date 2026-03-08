<?php
$authUsuario = \App\Services\AuthService::getLoggedUser();
?>
<header class="header">
    <div class="header__inner">
        <a href="/" class="header__brand" aria-label="MecaQuick - Inicio">
            <img src="/assets/img/logo_sena.png" alt="MecaQuick" class="header__logo-img" width="40" height="40">
            <span class="header__title"><?= htmlspecialchars('MecaQuick') ?></span>
        </a>
        <nav class="header__nav">
            <a href="/checklist" class="btn btn--secondary header__btn">Checklist vehículos</a>
            <?php if ($authUsuario !== null): ?>
                <a href="/dashboard" class="btn btn--secondary header__btn">Panel</a>
                <?php if (($authUsuario['rol'] ?? '') === 'admin'): ?>
                    <a href="/usuarios/aprendices" class="btn btn--secondary header__btn">Gestión Aprendices</a>
                    <a href="/usuarios/instructores" class="btn btn--secondary header__btn">Gestión Instructores</a>
                <?php endif; ?>
                <a href="/logout" class="btn btn--secondary header__btn">Cerrar sesión</a>
            <?php else: ?>
                <a href="/login" class="btn btn--secondary header__btn">Iniciar sesión</a>
            <?php endif; ?>
            <a href="/home/formulario" class="btn btn--primary header__cta">Solicitar cita</a>
        </nav>
    </div>
</header>
