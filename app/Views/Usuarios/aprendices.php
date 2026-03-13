<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'Aprendices') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main class="layout layout--usuarios">
        <section class="panel panel--usuarios">
            <div class="usuarios__header">
                <h1 class="panel__title">Gestión Aprendices</h1>
                <a href="/usuarios/crear?rol=aprendiz" class="btn btn--primary">Crear aprendiz</a>
            </div>
            <p class="panel__intro">Usuarios con rol aprendiz. Las credenciales se envían por correo al crearlos.</p>

            <?php if (empty($usuarios)): ?>
                <p class="usuarios__empty">No hay aprendices registrados. <a href="/usuarios/crear?rol=aprendiz">Crear uno</a>.</p>
            <?php else: ?>
                <div class="usuarios__table-wrapper">
                    <table class="usuarios__table">
                        <thead>
                            <tr>
                                <th class="usuarios__th usuarios__th--id">ID</th>
                                <th class="usuarios__th usuarios__th--nombre">Nombre Completo</th>
                                <th class="usuarios__th usuarios__th--email">Correo Electrónico</th>
                                <th class="usuarios__th usuarios__th--estado">Estado</th>
                                <th class="usuarios__th usuarios__th--acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr class="usuarios__row">
                                    <td class="usuarios__td usuarios__td--id"><?= htmlspecialchars($u['id']) ?></td>
                                    <td class="usuarios__td usuarios__td--nombre">
                                        <i class="fas fa-user-graduate usuarios__icon"></i>
                                        <?= htmlspecialchars($u['nombre']) ?>
                                    </td>
                                    <td class="usuarios__td usuarios__td--email"><?= htmlspecialchars($u['email']) ?></td>
                                    <td class="usuarios__td usuarios__td--estado">
                                        <?php if ((int) $u['activo'] === 1): ?>
                                            <span class="usuarios__badge usuarios__badge--activo">
                                                <i class="fas fa-check-circle"></i> Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="usuarios__badge usuarios__badge--inactivo">
                                                <i class="fas fa-times-circle"></i> Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="usuarios__td usuarios__td--acciones">
                                        <div class="usuarios__acciones">
                                            <a href="/usuarios/editar/<?= htmlspecialchars($u['id']) ?>" 
                                               class="usuarios__btn usuarios__btn--editar" 
                                               title="Editar usuario">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="usuarios__btn usuarios__btn--eliminar" 
                                                    onclick="confirmarEliminacion(<?= htmlspecialchars($u['id']) ?>, '<?= htmlspecialchars($u['nombre'], ENT_QUOTES) ?>')"
                                                    title="Eliminar usuario">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <script>
                function confirmarEliminacion(id, nombre) {
                    if (confirm(`¿Está seguro de eliminar al usuario "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                        window.location.href = `/usuarios/eliminar/${id}`;
                    }
                }
                </script>
            <?php endif; ?>

            <div class="usuarios__links">
                <a href="/usuarios/instructores" class="btn btn--secondary">Gestión Instructores</a>
                <a href="/dashboard" class="btn btn--secondary">Volver al panel</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
