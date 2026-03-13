<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Editar usuario') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--usuarios">
        <section class="panel panel--usuarios panel--editar-usuario">
            <div class="usuarios__editar-header">
                <h1 class="panel__title">
                    <i class="fas fa-<?= ($usuario['rol'] ?? '') === 'instructor' ? 'chalkboard-teacher' : 'user-graduate' ?>" style="color: var(--sena-green);"></i>
                    <?= ($usuario['rol'] ?? '') === 'instructor' ? 'Editar instructor' : 'Editar aprendiz' ?>
                </h1>
                <p class="panel__intro">Modifique los datos del usuario. Los cambios se aplicarán inmediatamente.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert--error" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="/usuarios/editar/<?= htmlspecialchars((string) $usuario['id']) ?>" method="post" class="form form--editar-usuario">
                <div class="form__content">
                    <div class="form__group">
                        <label for="nombre">Nombre completo *</label>
                        <input type="text" id="nombre" name="nombre" required
                               value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>">
                    </div>
                    
                    <div class="form__group">
                        <label for="email">Correo electrónico *</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                    </div>
                    
                    <div class="form__group form__group--checkbox">
                        <label class="form__checkbox-label">
                            <input type="checkbox" id="activo" name="activo" value="1" 
                                   <?= ((int) ($usuario['activo'] ?? 0) === 1) ? 'checked' : '' ?>>
                            <span>Usuario activo</span>
                        </label>
                        <span class="form__help">Si está inactivo, no podrá iniciar sesión.</span>
                    </div>
                </div>
                
                <div class="form__actions">
                    <button type="submit" class="btn btn--primary btn--with-icon">
                        <i class="fas fa-save btn__icon"></i> Guardar cambios
                    </button>
                    <a href="<?= ($usuario['rol'] ?? '') === 'instructor' ? '/usuarios/instructores' : '/usuarios/aprendices' ?>" class="btn btn--secondary btn--with-icon">
                        <i class="fas fa-times btn__icon"></i> Cancelar
                    </a>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
