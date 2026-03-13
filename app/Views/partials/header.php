<?php
$authUsuario = \App\Services\AuthService::getLoggedUser();
?>
<header class="header">
    <div class="header__inner">
        <a href="/" class="header__brand" aria-label="MecaQuick - Inicio">
            <img src="/assets/img/logo-sena_blanco.png" alt="MecaQuick" class="header__logo-img" width="40" height="40">
            <span class="header__title"><?= htmlspecialchars('MecaQuick') ?></span>
        </a>
        
        <button class="header__hamburger" id="headerHamburger" aria-label="Abrir menú" aria-expanded="false">
            <span class="header__hamburger-line"></span>
            <span class="header__hamburger-line"></span>
            <span class="header__hamburger-line"></span>
        </button>
        
        <nav class="header__nav" id="headerNav">
            <?php if ($authUsuario !== null): ?>
                <a href="/dashboard" class="btn btn--header header__btn">
                    <i class="fas fa-th-large"></i>
                    Dashboard
                </a>
                <a href="/cuenta" class="header__user-link">
                    <i class="fas fa-user"></i>
                    <span><?= htmlspecialchars($authUsuario['nombre'] ?? 'Usuario') ?></span>
                </a>
                <a href="/logout" class="header__logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar sesión
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

<script>
(function() {
    const hamburger = document.getElementById('headerHamburger');
    const nav = document.getElementById('headerNav');
    
    if (hamburger && nav) {
        hamburger.addEventListener('click', function() {
            const isExpanded = hamburger.getAttribute('aria-expanded') === 'true';
            hamburger.setAttribute('aria-expanded', !isExpanded);
            hamburger.classList.toggle('header__hamburger--active');
            nav.classList.toggle('header__nav--open');
        });
        
        document.addEventListener('click', function(event) {
            if (!hamburger.contains(event.target) && !nav.contains(event.target)) {
                hamburger.setAttribute('aria-expanded', 'false');
                hamburger.classList.remove('header__hamburger--active');
                nav.classList.remove('header__nav--open');
            }
        });
    }
})();
</script>
